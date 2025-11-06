<?php

namespace App\Http\Controllers;

use App\Models\LotteryTicket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:lottery-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:lottery-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:lottery-edit',   ['only' => ['edit', 'update']]);
        $this->middleware('permission:lottery-delete', ['only' => ['destroy']]);
    }

    // List Tickets
    public function index(Request $request)
    {
        $q = LotteryTicket::query();

        // Filtering
        if ($search = $request->input('search')) {
            $q->where(function($query) use ($search) {
                $query->where('bar_code', 'like', "%$search%")
                    ->orWhere('ticket_name', 'like', "%$search%")
                    ->orWhere('signature', 'like', "%$search%")
                    ->orWhere('period', 'like', "%$search%")
                    ->orWhere('big_num', 'like', "%$search%");
            });
        }
        if ($ticketType = $request->input('ticket_type')) {
            $q->where('ticket_type', $ticketType);
        }
        if ($withdrawDate = $request->input('withdraw_date')) {
            $q->whereDate('withdraw_date', $withdrawDate);
        }

        $tickets = $q->orderBy('withdraw_date', 'desc')->paginate(15)
            ->appends($request->query());

        return view('tickets.index', compact('tickets'));
    }

    // Show Create Form
    public function create()
    {
        $barcode = now()->format('ymdHis') . rand(1000000000, 9999999999);
        return view('tickets.create', compact('barcode'));
    }

    // Store New Ticket
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_name' => 'required|max:255',
            'signature' => 'nullable|max:255',
            'withdraw_date' => 'required|date',
            'ticket_type' => 'required|in:normal,special,lucky',
            'numbers' => 'required|array|size:6',
            'numbers.*' => 'required|digits:1',
            'bar_code' => 'required|unique:lottery_tickets',
            'period' => 'required|integer',
            'big_num' => 'nullable|integer',
            'set_no' => 'nullable|integer',
            'price' => 'required|numeric|min:0',
            'left_icon' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);
        // Handle file upload
        if($request->hasFile('left_icon')) {
            $validated['left_icon'] = $request->file('left_icon')->store('ticket_icons', 'public');
        }
        LotteryTicket::create($validated);

        return redirect()->route('tickets.index')->with('success', 'Ticket created!');
    }

    // Show Ticket Edit Form
    public function edit($id)
    {
        $ticket = LotteryTicket::findOrFail($id);
        return view('tickets.edit', compact('ticket'));
    }

    // Update Ticket
    public function update(Request $request, $id)
    {
        $ticket = LotteryTicket::findOrFail($id);
        
        $validated = $request->validate([
            'ticket_name' => 'required|max:255',
            'signature' => 'nullable|max:255',
            'withdraw_date' => 'required|date',
            'ticket_type' => 'required|in:normal,special,lucky',
            'numbers' => 'required|array|size:6',
            'numbers.*' => 'required|digits:1',
            'bar_code' => "required|unique:lottery_tickets,bar_code,$id",
            'period' => 'required|integer',
            'big_num' => 'nullable|integer',
            'set_no' => 'nullable|integer',
            'price' => 'required|numeric|min:0',
            'left_icon' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);
        
        // Handle file upload
        if($request->hasFile('left_icon')) {
            $validated['left_icon'] = $request->file('left_icon')->store('ticket_icons', 'public');
        }
        
        $ticket->update($validated);
        
        return redirect()->route('tickets.index')->with('success', 'Ticket updated!');
    }
    // Show Single Ticket
    public function show($id)
    {
        $ticket = LotteryTicket::findOrFail($id);
        return view('tickets.show', compact('ticket'));
    }

    // Delete Ticket
    public function destroy($id)
    {
        $ticket = LotteryTicket::findOrFail($id);
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted!');
    }
}
