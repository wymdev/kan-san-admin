<?php

namespace App\Http\Controllers;

use App\Models\SecondarySalesTransaction;
use App\Models\SecondaryLotteryTicket;
use App\Models\Customer;
use App\Services\LotteryResultCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecondarySalesDashboardController extends Controller
{
    protected $checkerService;

    public function __construct(LotteryResultCheckerService $checkerService)
    {
        $this->checkerService = $checkerService;
    }

    /**
     * Display the secondary sales dashboard
     */
    public function index(Request $request)
    {
        // Get date range (default: last 30 days)
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Basic statistics
        $stats = $this->checkerService->getTransactionStatistics();

        // Revenue statistics
        $revenueStats = $this->getRevenueStats($dateFrom, $dateTo);

        // Top buyers
        $topBuyers = $this->getTopBuyers($dateFrom, $dateTo, 10);

        // Win/Loss statistics
        $winLossStats = $this->getWinLossStats($dateFrom, $dateTo);

        // Recent transactions
        $recentTransactions = SecondarySalesTransaction::with(['secondaryTicket', 'customer'])
            ->latest('purchased_at')
            ->take(10)
            ->get();

        // Recent winners
        $recentWinners = SecondarySalesTransaction::with(['secondaryTicket', 'customer', 'drawResult'])
            ->where('status', 'won')
            ->latest('checked_at')
            ->take(5)
            ->get();

        // Sales trend (daily for the period)
        $salesTrend = $this->getSalesTrend($dateFrom, $dateTo);

        // Payment collection status
        $paymentStats = [
            'total_collected' => SecondarySalesTransaction::where('is_paid', true)
                ->whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('amount_thb'),
            'total_pending' => SecondarySalesTransaction::where('is_paid', false)
                ->whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->sum('amount_thb'),
            'paid_count' => SecondarySalesTransaction::where('is_paid', true)
                ->whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
            'unpaid_count' => SecondarySalesTransaction::where('is_paid', false)
                ->whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
        ];

        // Tickets statistics
        $ticketStats = [
            'total_tickets' => SecondaryLotteryTicket::count(),
            'tickets_sold' => SecondaryLotteryTicket::has('transactions')->count(),
            'tickets_unsold' => SecondaryLotteryTicket::doesntHave('transactions')->count(),
            'tickets_today' => SecondaryLotteryTicket::whereDate('created_at', today())->count(),
        ];

        return view('secondary-sales.dashboard.index', compact(
            'stats',
            'revenueStats',
            'topBuyers',
            'winLossStats',
            'recentTransactions',
            'recentWinners',
            'salesTrend',
            'paymentStats',
            'ticketStats',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Get revenue statistics
     */
    private function getRevenueStats($dateFrom, $dateTo)
    {
        $periodRevenue = SecondarySalesTransaction::whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->sum('amount_thb');

        $previousPeriodStart = Carbon::parse($dateFrom)->subDays(Carbon::parse($dateFrom)->diffInDays($dateTo));
        $previousPeriodRevenue = SecondarySalesTransaction::whereBetween('purchased_at', [
            $previousPeriodStart->format('Y-m-d'),
            Carbon::parse($dateFrom)->subDay()->format('Y-m-d') . ' 23:59:59'
        ])->sum('amount_thb');

        $revenueChange = $previousPeriodRevenue > 0
            ? (($periodRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100
            : 0;

        return [
            'period_revenue' => $periodRevenue,
            'previous_revenue' => $previousPeriodRevenue,
            'revenue_change' => round($revenueChange, 1),
            'total_revenue' => SecondarySalesTransaction::sum('amount_thb'),
            'average_transaction' => SecondarySalesTransaction::whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->avg('amount_thb') ?? 0,
            'transaction_count' => SecondarySalesTransaction::whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
        ];
    }

    /**
     * Get top buyers
     */
    private function getTopBuyers($dateFrom, $dateTo, $limit = 10)
    {
        // Get buyers with customer accounts
        $registeredBuyers = SecondarySalesTransaction::with('customer')
            ->whereNotNull('customer_id')
            ->whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select('customer_id')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(amount_thb) as total_spent')
            ->selectRaw('SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as wins')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->take($limit)
            ->get();

        // Get unregistered buyers by phone
        $unregisteredBuyers = SecondarySalesTransaction::whereNull('customer_id')
            ->whereNotNull('customer_phone')
            ->whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->select('customer_name', 'customer_phone')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(amount_thb) as total_spent')
            ->selectRaw('SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as wins')
            ->groupBy('customer_name', 'customer_phone')
            ->orderByDesc('total_spent')
            ->take($limit)
            ->get();

        // Merge and sort
        $allBuyers = collect();

        foreach ($registeredBuyers as $buyer) {
            $allBuyers->push([
                'name' => $buyer->customer->full_name ?? 'Unknown',
                'phone' => $buyer->customer->phone_number ?? '-',
                'is_registered' => true,
                'transaction_count' => $buyer->transaction_count,
                'total_spent' => $buyer->total_spent,
                'wins' => $buyer->wins,
            ]);
        }

        foreach ($unregisteredBuyers as $buyer) {
            $allBuyers->push([
                'name' => $buyer->customer_name ?? 'Unknown',
                'phone' => $buyer->customer_phone ?? '-',
                'is_registered' => false,
                'transaction_count' => $buyer->transaction_count,
                'total_spent' => $buyer->total_spent,
                'wins' => $buyer->wins,
            ]);
        }

        return $allBuyers->sortByDesc('total_spent')->take($limit)->values();
    }

    /**
     * Get win/loss statistics
     */
    private function getWinLossStats($dateFrom, $dateTo)
    {
        $checked = SecondarySalesTransaction::whereNotNull('checked_at')
            ->whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59']);

        $won = (clone $checked)->where('status', 'won')->count();
        $notWon = (clone $checked)->where('status', 'not_won')->count();
        $total = $won + $notWon;

        return [
            'won' => $won,
            'not_won' => $notWon,
            'total_checked' => $total,
            'win_rate' => $total > 0 ? round(($won / $total) * 100, 1) : 0,
            'pending_check' => SecondarySalesTransaction::where('status', 'pending')
                ->whereNull('checked_at')
                ->whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->count(),
        ];
    }

    /**
     * Get sales trend data for chart
     */
    private function getSalesTrend($dateFrom, $dateTo)
    {
        $trend = SecondarySalesTransaction::whereBetween('purchased_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('DATE(purchased_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount_thb) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $trend->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d'),
                'count' => $item->count,
                'revenue' => $item->revenue,
            ];
        });
    }
}
