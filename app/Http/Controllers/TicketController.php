<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
        // $customer = Customer::where('username', $request->username)->first();

        $ticket = Ticket::create([
            'ticket_no' => 'TKT-' . time(),
            'customer_id' => $request->customer_id,
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority,
            'status' => 'open',
        ]);

        // initial message as reply
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'customer_id' => $request->customer_id,
            'message' => $request->message,
        ]);


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
    public function destroy(Ticket $ticket)
    {
        //
    }


    public function assign(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'in_progress',
            'assigned_at' => now(),
        ]);

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

        $data = [
            'status' => $request->status,
        ];

        if ($request->status === 'closed') {
            $data['closed_at'] = now();
        }

        $ticket->update($data);

        return back()->with('success', 'Status updated');
    }
}
