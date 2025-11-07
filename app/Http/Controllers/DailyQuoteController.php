<?php

namespace App\Http\Controllers;

use App\Models\DailyQuote;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DailyQuoteController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->middleware('permission:mobile-app-manage', ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]);
        $this->pushService = $pushService;
    }

    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $category = $request->input('category', '');
        
        $query = DailyQuote::query();
        
        if (!empty($search)) {
            $query->where('quote', 'like', '%' . $search . '%')
                  ->orWhere('author', 'like', '%' . $search . '%');
        }
        
        if ($status === 'sent') {
            $query->where('is_sent', true);
        } elseif ($status === 'pending') {
            $query->where('is_sent', false)->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if (!empty($category)) {
            $query->where('category', $category);
        }
        
        $quotes = $query->orderBy('created_at', 'DESC')->paginate(15);
        
        return view('daily-quotes.index', compact('quotes', 'search', 'status', 'category'))
            ->with('i', ($request->input('page', 1) - 1) * 15);
    }

    public function create(): View
    {
        return view('daily-quotes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'quote' => 'required|string|max:500',
            'author' => 'nullable|string|max:255',
            'category' => 'required|in:motivation,inspiration,success,luck',
            'is_active' => 'boolean',
            'scheduled_for' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            DailyQuote::create($validated);
            
            return redirect()->route('daily-quotes.index')
                ->with('success', 'Daily quote created successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create quote: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id): View
    {
        $quote = DailyQuote::findOrFail($id);
        return view('daily-quotes.show', compact('quote'));
    }

    public function edit($id): View
    {
        $quote = DailyQuote::findOrFail($id);
        return view('daily-quotes.edit', compact('quote'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        try {
            $quote = DailyQuote::findOrFail($id);

            $validated = $request->validate([
                'quote' => 'required|string|max:500',
                'author' => 'nullable|string|max:255',
                'category' => 'required|in:motivation,inspiration,success,luck',
                'is_active' => 'boolean',
                'scheduled_for' => 'nullable|date|after_or_equal:today',
            ]);

            $quote->update($validated);

            return redirect()->route('daily-quotes.index')
                ->with('success', 'Daily quote updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update quote: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $quote = DailyQuote::findOrFail($id);
            $quote->delete();

            return redirect()->route('daily-quotes.index')
                ->with('success', 'Daily quote deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('daily-quotes.index')
                ->with('error', 'Failed to delete quote.');
        }
    }

    /**
     * Send a specific quote immediately
     */
    public function sendNow($id): RedirectResponse
    {
        try {
            $quote = DailyQuote::findOrFail($id);

            if ($quote->is_sent) {
                return back()->with('warning', 'This quote has already been sent.');
            }

            $title = 'Daily Quote';
            $body = strlen($quote->quote) > 100 
                ? substr($quote->quote, 0, 97) . '...' 
                : $quote->quote;

            $results = $this->pushService->broadcastAnnouncement(
                title: $title,
                body: $body,
                data: [
                    'quote_id' => $quote->id,
                    'type' => 'daily_quote',
                    'full_quote' => $quote->quote,
                    'author' => $quote->author,
                    'category' => $quote->category,
                ]
            );

            $successCount = collect($results)->filter()->count();

            $quote->update([
                'is_sent' => true,
                'sent_at' => now(),
                'recipients_count' => count($results),
            ]);

            return back()->with('success', "Quote sent to {$successCount} customers!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send quote: ' . $e->getMessage());
        }
    }
}
