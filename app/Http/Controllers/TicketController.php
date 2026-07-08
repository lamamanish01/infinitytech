<?php

namespace App\Http\Controllers;

use App\Helpers\Activity;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('permission:view tickets')->only(['index', 'show']);
        $this->middleware('permission:create tickets')->only(['create', 'store']);
        $this->middleware('permission:edit tickets')->only(['edit', 'update']);
        $this->middleware('permission:delete tickets')->only(['destroy']);

        $this->middleware('permission:assign tickets')->only(['assign']);
        $this->middleware('permission:reply tickets')->only(['reply', 'customerReply']);
        $this->middleware('permission:note tickets')->only(['internalNote']);
        $this->middleware('permission:close tickets')->only(['internalNote']);
        $this->middleware('permission:status tickets')->only(['updateStatus']);
    }

    public function index()
    {
        $tickets = Ticket::with(['customer','assignedUser'])
            ->latest()
            ->paginate(20);
        return view('ticket.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::all();
        return view('ticket.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        $ticket = Ticket::create([
            'ticket_no' => 'TKT-' . time(),
            'customer_id' => $request->customer_id,
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'customer_id' => $request->customer_id,
            'message' => $request->message,
        ]);

        $customer = Customer::find($request->customer_id);

        Activity::add(
            'Ticket Created',
            'Ticket #' . $ticket->ticket_no . ' created by ' . $customer->username,
            'fas fa-ticket-alt text-success',
            $customer->username,
            route('ticket.show', $ticket->id)
        );

        return redirect()->route('ticket.index')->with('success', 'Ticket created sucessfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ticket = Ticket::with([
            'customer',
            'creator',
            'assignedUser',
            'replies.user',
            'replies.customer'
        ])->findOrFail($id);

        $users = User::all();

        return view('ticket.show', compact('ticket', 'users'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return redirect()->route('ticket.index')->with('success', 'Ticket Delete sucessfully');
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);
        $user = User::findOrFail($request->assigned_to);  // the assigned user
        $customer = $ticket->customer;

        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'in_progress',
            'assigned_at' => now(),
        ]);

        $user->notify(new TicketAssignedNotification($ticket));

        Activity::add(
            'Ticket Assigned',
            'Ticket #' . $ticket->ticket_no . ' assigned to ' . $user->name . ' (Customer: ' . $customer->username . ')',
            'fas fa-user-check text-primary',
            $customer->username,
            route('ticket.show', $ticket->id)
        );

        return back()->with('success', 'Ticket assigned successfully');
    }

    /**
     * STAFF REPLY
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        $customer = $ticket->customer;

        Activity::add(
            'Ticket Reply',
            'Staff replied on Ticket #' . $ticket->ticket_no . ' (Customer: ' . $customer->username . ')',
            'fas fa-reply text-info',
            $customer->username,
            route('ticket.show', $ticket->id)
        );

        return back()->with('success', 'Reply sent');
    }

    /**
     * CUSTOMER REPLY
     */
    public function customerReply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
        ]);

        $ticket = Ticket::findOrFail($id);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'customer_id' => $request->customer_id,
            'message' => $request->message,
        ]);

        $customer = $ticket->customer;

        Activity::add(
            'Customer Reply',
            $customer->username . ' replied on Ticket #' . $ticket->ticket_no,
            'fas fa-comment text-warning',
            $customer->username,
            route('ticket.show', $ticket->id)
        );

        return back()->with('success', 'Message sent');
    }

    /**
     * INTERNAL NOTE (STAFF ONLY)
     */
    public function internalNote(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_internal' => true,
        ]);

        $customer = $ticket->customer;

        Activity::add(
            'Internal Note Added',
            'Internal note added on Ticket #' . $ticket->ticket_no . ' (Customer: ' . $customer->username . ')',
            'fas fa-sticky-note text-secondary',
            $customer->username,
            route('ticket.show', $ticket->id)
        );

        return back()->with('success', 'Internal note added');
    }

    /**
     * UPDATE STATUS
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket = Ticket::findOrFail($id);

        $oldStatus = $ticket->status;

        $data = [
            'status' => $request->status,
        ];

        if ($request->status === 'closed') {
            $data['closed_at'] = now();
        }

        $ticket->update($data);

        $customer = $ticket->customer;

        Activity::add(
            'Ticket Status Updated',
            'Ticket #' . $ticket->ticket_no .
            ' changed from ' . $oldStatus .
            ' to ' . $request->status .
            ' (Customer: ' . $customer->username . ')',
            'fas fa-sync-alt text-primary',
            $customer->username,
            route('ticket.show', $ticket->id)
        );

        return back()->with('success', 'Status updated');
    }
}
