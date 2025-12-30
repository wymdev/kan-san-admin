<?php

namespace App\Http\Controllers;

use App\Models\SecondaryLotteryTicket;
use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SecondaryTicketController extends Controller
{
    protected $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Display a listing of secondary tickets
     */
    public function index(Request $request)
    {
        $query = SecondaryLotteryTicket::with(['createdBy', 'transactions']);

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('bar_code', 'like', "%{$search}%")
                  ->orWhere('source_seller', 'like', "%{$search}%")
                  ->orWhereRaw("JSON_EXTRACT(numbers, '$') LIKE ?", ["%{$search}%"]);
            });
        }

        // Draw date filter
        if ($request->filled('withdraw_date')) {
            $query->whereDate('withdraw_date', $request->withdraw_date);
        }

        // Created date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Status filter based on transactions
        if ($request->input('has_transactions') === 'yes') {
            $query->has('transactions');
        } elseif ($request->input('has_transactions') === 'no') {
            $query->doesntHave('transactions');
        }

        $tickets = $query->latest()->paginate(20)->appends($request->query());

        // Get upcoming draw dates for filter dropdown
        $drawDates = SecondaryLotteryTicket::distinct()
            ->whereNotNull('withdraw_date')
            ->orderBy('withdraw_date', 'desc')
            ->pluck('withdraw_date');

        // Get statistics from database (not from paginated collection)
        $ticketStats = [
            'total' => SecondaryLotteryTicket::count(),
            'sold' => SecondaryLotteryTicket::has('transactions')->count(),
            'unsold' => SecondaryLotteryTicket::doesntHave('transactions')->count(),
        ];

        return view('secondary-sales.tickets.index', compact('tickets', 'drawDates', 'ticketStats'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        return view('secondary-sales.tickets.create');
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'numbers' => 'required|string|min:6',
            'batch_number' => 'nullable|string|max:50',
            'withdraw_date' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
            'source_seller' => 'nullable|string|max:255',
            'source_image' => 'nullable|image|max:5120', // 5MB max
            'notes' => 'nullable|string|max:1000',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('source_image')) {
            $imagePath = $request->file('source_image')->store('secondary-tickets', 'public');
        }

        // Parse numbers - can be comma-separated for multiple tickets
        $numbersInput = $request->input('numbers');
        $numbersArray = array_map('trim', explode(',', $numbersInput));
        
        $batchNumber = $request->input('batch_number');
        $createdTickets = [];
        
        foreach ($numbersArray as $numberStr) {
            $numberStr = preg_replace('/\D/', '', $numberStr); // Remove non-digits
            
            if (strlen($numberStr) >= 6) {
                // Store as array of individual digits (like primary tickets)
                $digits = str_split(substr($numberStr, 0, 6));
                
                $ticket = SecondaryLotteryTicket::create([
                    'batch_number' => $batchNumber,
                    'ticket_name' => $request->input('ticket_name', 'Secondary Ticket'),
                    'signature' => $request->input('signature'),
                    'withdraw_date' => $request->input('withdraw_date'),
                    'ticket_type' => $request->input('ticket_type', 'normal'),
                    'numbers' => $digits,
                    'bar_code' => $request->input('bar_code'),
                    'period' => $request->input('period'),
                    'big_num' => $request->input('big_num'),
                    'set_no' => $request->input('set_no'),
                    'price' => $request->input('price'),
                    'source_image' => $imagePath,
                    'source_seller' => $request->input('source_seller'),
                    'notes' => $request->input('notes'),
                    'created_by' => auth()->id(),
                ]);
                
                $createdTickets[] = $ticket;
            }
        }

        if (count($createdTickets) === 0) {
            return back()->withInput()->with('error', 'No valid 6-digit numbers found. Please enter valid lottery numbers.');
        }

        $message = count($createdTickets) === 1 
            ? 'Ticket created successfully!' 
            : count($createdTickets) . ' tickets created successfully!';

        return redirect()->route('secondary-tickets.index')->with('success', $message);
    }

    /**
     * Display the specified ticket
     */
    public function show(SecondaryLotteryTicket $secondaryTicket)
    {
        $secondaryTicket->load(['createdBy', 'transactions.customer', 'transactions.drawResult']);
        
        return view('secondary-sales.tickets.show', compact('secondaryTicket'));
    }

    /**
     * Show the form for editing the specified ticket
     */
    public function edit(SecondaryLotteryTicket $secondaryTicket)
    {
        return view('secondary-sales.tickets.edit', compact('secondaryTicket'));
    }

    /**
     * Update the specified ticket
     */
    public function update(Request $request, SecondaryLotteryTicket $secondaryTicket)
    {
        $request->validate([
            'numbers' => 'required|string|min:6',
            'withdraw_date' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
            'source_seller' => 'nullable|string|max:255',
            'source_image' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Handle image upload
        if ($request->hasFile('source_image')) {
            // Delete old image
            if ($secondaryTicket->source_image) {
                Storage::disk('public')->delete($secondaryTicket->source_image);
            }
            $secondaryTicket->source_image = $request->file('source_image')->store('secondary-tickets', 'public');
        }

        // Parse numbers
        $numberStr = preg_replace('/\D/', '', $request->input('numbers'));
        $digits = str_split(substr($numberStr, 0, 6));

        $secondaryTicket->update([
            'ticket_name' => $request->input('ticket_name', 'Secondary Ticket'),
            'signature' => $request->input('signature'),
            'withdraw_date' => $request->input('withdraw_date'),
            'ticket_type' => $request->input('ticket_type', 'normal'),
            'numbers' => $digits,
            'bar_code' => $request->input('bar_code'),
            'period' => $request->input('period'),
            'big_num' => $request->input('big_num'),
            'set_no' => $request->input('set_no'),
            'price' => $request->input('price'),
            'source_seller' => $request->input('source_seller'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('secondary-tickets.index')->with('success', 'Ticket updated successfully!');
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(SecondaryLotteryTicket $secondaryTicket)
    {
        // Check if ticket has transactions
        if ($secondaryTicket->transactions()->exists()) {
            return back()->with('error', 'Cannot delete ticket with existing transactions. Delete transactions first.');
        }

        // Delete image if exists
        if ($secondaryTicket->source_image) {
            Storage::disk('public')->delete($secondaryTicket->source_image);
        }

        $secondaryTicket->delete();

        return redirect()->route('secondary-tickets.index')->with('success', 'Ticket deleted successfully!');
    }

    /**
     * Extract numbers from uploaded image via OCR
     */
    public function extractOcr(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        // Store the image temporarily
        $path = $request->file('image')->store('temp-ocr', 'public');

        // Run OCR
        $result = $this->ocrService->extractNumbersFromImage($path);

        // Clean up temp file
        Storage::disk('public')->delete($path);

        return response()->json($result);
    }
}
