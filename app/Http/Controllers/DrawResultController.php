<?php

namespace App\Http\Controllers;

use App\Support\DatabaseSql;
use App\Support\PrizeLabels;

use App\Models\DrawResult;
use Illuminate\Http\Request;
use App\Services\PublicLotteryResultsService;

class DrawResultController extends Controller
{
    public function index(Request $request)
    {
        $query = DrawResult::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereLike('date_th', '%' . $request->search . '%')
                    ->orWhereLike('date_en', '%' . $request->search . '%');
            });
        }

        // Draw date filter
        if ($request->filled('draw_date')) {
            $query->whereDate('draw_date', $request->draw_date);
        }

        // Year filter
        if ($request->filled('year')) {
            $query->whereRaw(DatabaseSql::part('draw_date', 'year').' = ?', [(int) $request->year]);
        }

        // Month filter
        if ($request->filled('month')) {
            $query->whereRaw(DatabaseSql::part('draw_date', 'month').' = ?', [(int) $request->month]);
        }

        // Sort — restricted to known columns so the parameter cannot reach raw SQL.
        $sortBy = in_array($request->get('sort_by'), ['draw_date', 'date_en'], true)
            ? $request->get('sort_by')
            : 'draw_date';
        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        $results = $query->paginate(15);

        // Get available years for filter
        $years = DrawResult::selectRaw(DatabaseSql::part('draw_date', 'year').' as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $historyImported = app(PublicLotteryResultsService::class)->historyImported();
        return view('draw_results.index', compact('results', 'years', 'historyImported'));
    }

    // Show detail page
    public function showDetail($id)
    {
        $result = DrawResult::findOrFail($id);

        return view('draw_results.show', [
            'result' => $result,
            'prizes' => PrizeLabels::normalise($result->prizes),
            'running_numbers' => PrizeLabels::normalise($result->running_numbers),
        ]);
    }

    // Details for modal: AJAX JSON
    public function show($id)
    {
        $result = DrawResult::findOrFail($id);

        return response()->json([
            'date_en' => $result->date_en,
            'date_th' => $result->date_th,
            'endpoint' => $result->endpoint,
            'prizes' => PrizeLabels::normalise($result->prizes),
            'running_numbers' => PrizeLabels::normalise($result->running_numbers),
        ]);
    }

    public function syncLatest(PublicLotteryResultsService $service)
    {
        try {
            $service->syncLatest();
            return back()->with('success', 'Latest lottery result synced successfully.');
        } catch (\Throwable $exception) {
            return back()->with('error', 'Unable to sync results. Existing results were preserved. Check the results API and database connection.');
        }
    }

    public function syncAll(PublicLotteryResultsService $service)
    {
        try {
            $count = $service->importHistoryOnce();
            return back()->with($count ? 'success' : 'info', $count
                ? "Imported {$count} historical draw result(s). History import is now complete."
                : 'History was already imported. No API request was made.');
        } catch (\Throwable $exception) {
            return back()->with('error', 'Unable to import history. Existing results were preserved; the import has not been marked complete.');
        }
    }
}
