<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tickets = Ticket::orderBy('id', 'asc')->paginate(10);
        return view('ticket.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::get();
        return view('ticket.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        $customer = Customer::where('username', $request->username)->first();

        Ticket::create([
            'customer_id' => $customer->id,
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority,
        ]);

        return redirect()->back()->with('success', 'Ticket created sucessfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        return view('ticket.show', compact('ticket'));
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

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required'
        ]);

        try {
            $ticket = Ticket::findOrFail($id);

            if ($ticket->status == 'closed') {

                return back()->with('error', 'Ticket already closed');
            }

            TicketReply::create([
                'ticket_id' => $id,
                'user_id' => auth()->id(),
                'message' => $request->message

            ]);

            return back()->with('success', 'Ticket Reply successfully');

            } catch (\Exception $e) {
                return back()->with('error',$e->getMessage());
        }
    }

    public function close(Request $request, $id)
    {
        try {

            $ticket = Ticket::findOrFail($id);

            $ticket->update([
                'status' => 'closed'
            ]);

            // $ticket->update([
            //     'status' => 'in_progress'
            // ]);

            return back()->with('success', 'Ticket closed successfully');

        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
