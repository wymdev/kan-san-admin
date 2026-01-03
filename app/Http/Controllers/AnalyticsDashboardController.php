<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\TicketPurchase;
use App\Models\LotteryTicket;
use App\Models\DevicePushToken;
use App\Models\ActivityLog;
use App\Models\SecondarySalesTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsDashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period);
        
        // Get all business insights data
        $data = [
            'period' => $period,
            'dateRange' => $dateRange,
            
            // Key Performance Indicators
            'kpis' => $this->getKPIs($dateRange),
            
            // Revenue Analytics
            'revenueData' => $this->getRevenueAnalytics($dateRange),
            
            // Customer Analytics
            'customerData' => $this->getCustomerAnalytics($dateRange),
            
            // Sales Analytics
            'salesData' => $this->getSalesAnalytics($dateRange),
            
            // Product Performance
            'productData' => $this->getProductPerformance($dateRange),
            
            // Platform Analytics
            'platformData' => $this->getPlatformAnalytics(),
            
            // Activity Trends
            'activityData' => $this->getActivityTrends($dateRange),
            
            // Winning Analytics
            'winningData' => $this->getWinningAnalytics($dateRange),
            
            // NEW: Advanced Business Insights
            'advancedInsights' => $this->getAdvancedInsights($dateRange),

            // Secondary Sales Module
            'secondarySales' => $this->getSecondarySalesData($dateRange),
        ];
        
        return view('dashboards.analytics', $data);
    }
    
    private function getDateRange($period)
    {
        switch ($period) {
            case 'today':
                $start = Carbon::today()->startOfDay();
                $end = Carbon::now();
                break;
            case 'week':
                $start = Carbon::now()->subWeek()->startOfDay();
                $end = Carbon::now();
                break;
            case 'month':
                $start = Carbon::now()->subMonth()->startOfDay();
                $end = Carbon::now();
                break;
            case '3months':
                $start = Carbon::now()->subMonths(3)->startOfDay();
                $end = Carbon::now();
                break;
            case '6months':
                $start = Carbon::now()->subMonths(6)->startOfDay();
                $end = Carbon::now();
                break;
            case 'year':
                $start = Carbon::now()->subYear()->startOfDay();
                $end = Carbon::now();
                break;
            default:
                $start = Carbon::now()->subMonth()->startOfDay();
                $end = Carbon::now();
        }
        
        return ['start' => $start, 'end' => $end];
    }
    
    private function getKPIs($dateRange)
    {
        // Calculate previous period for comparison
        $duration = $dateRange['end']->diffInDays($dateRange['start']);
        $prevStart = $dateRange['start']->copy()->subDays($duration);
        $prevEnd = $dateRange['start']->copy()->subSeconds(1);
        
        // Total Users
        $totalUsers = Customer::where('created_at', '<=', $dateRange['end'])->count();
        $prevUsers = Customer::where('created_at', '<=', $prevEnd)->count();
        $userGrowth = $prevUsers > 0 ? (($totalUsers - $prevUsers) / $prevUsers) * 100 : 0;
        
        // Total Revenue
        $totalRevenue = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->sum('total_price');
        $prevRevenue = TicketPurchase::whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->sum('total_price');
        $revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
        
        // Total Sessions (using activity logs)
        $totalSessions = ActivityLog::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->distinct('actor_id')
            ->count('actor_id');
        
        // Average Visit Duration (mock data - would need session tracking)
        $avgDuration = '1M 29sec';
        
        // Bounce Rate (mock calculation)
        $bounceRate = 49.77;
        
        return [
            'totalUsers' => $totalUsers,
            'userGrowth' => round($userGrowth, 2),
            'totalRevenue' => $totalRevenue,
            'revenueGrowth' => round($revenueGrowth, 2),
            'totalSessions' => $totalSessions,
            'avgDuration' => $avgDuration,
            'bounceRate' => $bounceRate,
        ];
    }
    
    private function getRevenueAnalytics($dateRange)
    {
        $groupBy = $this->getGroupByFormat($dateRange);
        
        // Revenue trend over time
        $revenueTrend = TicketPurchase::selectRaw("
                DATE_FORMAT(created_at, '{$groupBy}') as period,
                SUM(total_price) as revenue,
                COUNT(*) as orders
            ")
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        // Revenue by status
        $revenueByStatus = TicketPurchase::selectRaw("
                status,
                SUM(total_price) as revenue,
                COUNT(*) as count
            ")
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('status')
            ->get();
        
        return [
            'trend' => [
                'labels' => $revenueTrend->pluck('period')->toArray(),
                'revenue' => $revenueTrend->pluck('revenue')->map(fn($v) => (float)$v)->toArray(),
                'orders' => $revenueTrend->pluck('orders')->map(fn($v) => (int)$v)->toArray(),
            ],
            'byStatus' => [
                'labels' => $revenueByStatus->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))->toArray(),
                'values' => $revenueByStatus->pluck('revenue')->map(fn($v) => (float)$v)->toArray(),
            ],
        ];
    }
    
    private function getCustomerAnalytics($dateRange)
    {
        $groupBy = $this->getGroupByFormat($dateRange);
        
        // New customers over time
        $newCustomers = Customer::selectRaw("
                DATE_FORMAT(created_at, '{$groupBy}') as period,
                COUNT(*) as count
            ")
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        // Active customers (made purchase)
        $activeCustomers = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->distinct('customer_id')
            ->count('customer_id');
        
        // Gender distribution
        $genderDist = Customer::selectRaw("
                COALESCE(gender, 'Unknown') as gender,
                COUNT(*) as count
            ")
            ->groupBy('gender')
            ->get();
        
        // Age distribution
        $ageDist = $this->getAgeDistribution();
        
        return [
            'newCustomers' => [
                'labels' => $newCustomers->pluck('period')->toArray(),
                'counts' => $newCustomers->pluck('count')->map(fn($v) => (int)$v)->toArray(),
            ],
            'activeCustomers' => $activeCustomers,
            'genderDistribution' => [
                'labels' => $genderDist->pluck('gender')->map(fn($g) => ucfirst($g))->toArray(),
                'values' => $genderDist->pluck('count')->map(fn($v) => (int)$v)->toArray(),
            ],
            'ageDistribution' => $ageDist,
        ];
    }
    
    private function getSalesAnalytics($dateRange)
    {
        // Total sales
        $totalSales = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->count();
        
        // Average order value
        $avgOrderValue = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->avg('total_price');
        
        // Pending orders
        $pendingOrders = TicketPurchase::where('status', 'pending')->count();
        
        // Rejected orders
        $rejectedOrders = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'rejected')
            ->count();
        
        return [
            'totalSales' => $totalSales,
            'avgOrderValue' => round($avgOrderValue ?? 0, 2),
            'pendingOrders' => $pendingOrders,
            'rejectedOrders' => $rejectedOrders,
        ];
    }
    
    private function getProductPerformance($dateRange)
    {
        // All selling tickets (removed limit to show all)
        $topTickets = TicketPurchase::selectRaw("
                lottery_ticket_id,
                COUNT(*) as sales_count,
                SUM(total_price) as total_revenue,
                SUM(quantity) as total_quantity
            ")
            ->with('lotteryTicket')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->groupBy('lottery_ticket_id')
            ->orderByDesc('total_revenue')
            ->get();
        
        // Ticket type distribution
        $ticketTypes = LotteryTicket::selectRaw("
                ticket_type,
                COUNT(*) as count
            ")
            ->groupBy('ticket_type')
            ->get();
        
        return [
            'topTickets' => $topTickets,
            'ticketTypes' => $ticketTypes,
        ];
    }

    private function getPlatformAnalytics()
    {
        // Device distribution - show ALL devices, not just active
        $deviceDist = DevicePushToken::selectRaw("
                COALESCE(platform, 'Unknown') as platform,
                COUNT(*) as total_devices,
                COUNT(DISTINCT customer_id) as unique_users
            ")
            ->groupBy('platform')
            ->get();
        
        // If no data, provide default empty structure
        if ($deviceDist->isEmpty()) {
            return [
                'deviceDistribution' => [
                    'labels' => ['No Data'],
                    'values' => [0],
                    'devices' => [0],
                ],
                'totalDevices' => 0,
                'uniqueUsers' => 0,
            ];
        }
        
        return [
            'deviceDistribution' => [
                'labels' => $deviceDist->pluck('platform')->toArray(),
                'values' => $deviceDist->pluck('total_devices')->map(fn($v) => (int)$v)->toArray(),
                'devices' => $deviceDist->pluck('total_devices')->map(fn($v) => (int)$v)->toArray(),
            ],
            'totalDevices' => $deviceDist->sum('total_devices'),
            'uniqueUsers' => $deviceDist->sum('unique_users'),
        ];
    }
    
    private function getActivityTrends($dateRange)
    {
        $groupBy = $this->getGroupByFormat($dateRange);
        
        // Daily activity
        $dailyActivity = ActivityLog::selectRaw("
                DATE_FORMAT(created_at, '{$groupBy}') as period,
                COUNT(*) as activities,
                COUNT(DISTINCT actor_id) as unique_users
            ")
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        return [
            'labels' => $dailyActivity->pluck('period')->toArray(),
            'activities' => $dailyActivity->pluck('activities')->toArray(),
            'uniqueUsers' => $dailyActivity->pluck('unique_users')->toArray(),
        ];
    }
    
    private function getWinningAnalytics($dateRange)
    {
        // Win/Loss distribution
        $wonCount = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'won')
            ->count();
        
        $lostCount = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'not_won')
            ->count();
        
        // Total prizes
        $totalPrizes = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'won')
            ->sum('prize_won');
        
        // Win rate
        $totalChecked = $wonCount + $lostCount;
        $winRate = $totalChecked > 0 ? ($wonCount / $totalChecked) * 100 : 0;
        
        return [
            'wonCount' => $wonCount,
            'lostCount' => $lostCount,
            'totalPrizes' => $totalPrizes,
            'winRate' => round($winRate, 2),
        ];
    }
    
    private function getAgeDistribution()
    {
        $customers = Customer::selectRaw("
                TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age
            ")
            ->whereNotNull('dob')
            ->get();
        
        $ageGroups = [
            '18-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-55' => 0,
            '56+' => 0
        ];
        
        foreach ($customers as $customer) {
            $age = $customer->age;
            if ($age >= 18 && $age <= 25) $ageGroups['18-25']++;
            elseif ($age >= 26 && $age <= 35) $ageGroups['26-35']++;
            elseif ($age >= 36 && $age <= 45) $ageGroups['36-45']++;
            elseif ($age >= 46 && $age <= 55) $ageGroups['46-55']++;
            elseif ($age > 55) $ageGroups['56+']++;
        }
        
        return $ageGroups;
    }
    
    private function getGroupByFormat($dateRange)
    {
        $days = $dateRange['end']->diffInDays($dateRange['start']);
        
        if ($days <= 1) {
            return '%Y-%m-%d %H:00'; // Hourly
        } elseif ($days <= 31) {
            return '%Y-%m-%d'; // Daily
        } elseif ($days <= 90) {
            return '%Y Week %u'; // Weekly
        } else {
            return '%Y-%m'; // Monthly
        }
    }

    private function getAdvancedInsights($dateRange)
    {
        // 1. Customer Lifetime Value (CLV)
        $customerSpending = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->select('customer_id', DB::raw('SUM(total_price) as total_spent, COUNT(*) as purchase_count'))
            ->groupBy('customer_id')
            ->get();

        $avgCLV = $customerSpending->avg('total_spent');
        $maxCLV = $customerSpending->max('total_spent');
        $highValueCustomers = $customerSpending->where('total_spent', '>=', 10000)->count();

        // 2. Retention Rate (customers who purchased more than once)
        $repeatCustomers = $customerSpending->where('purchase_count', '>', 1)->count();
        $totalCustomers = $customerSpending->count();
        $retentionRate = $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;

        // 3. Peak Hours Analysis
        $peakHours = TicketPurchase::selectRaw('
                HOUR(created_at) as hour,
                COUNT(*) as orders,
                SUM(total_price) as revenue
            ')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $busiestHour = $peakHours->sortByDesc('orders')->first();

        // 4. Day of Week Analysis
        $dayOfWeek = TicketPurchase::selectRaw('
                DAYNAME(created_at) as day,
                DAYOFWEEK(created_at) as day_num,
                COUNT(*) as orders,
                SUM(total_price) as revenue
            ')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->groupBy('day', 'day_num')
            ->orderBy('day_num')
            ->get();

        $bestDay = $dayOfWeek->sortByDesc('revenue')->first();

        // 5. Prize Payout Ratio
        $totalRevenue = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->sum('total_price');
        $totalPrizes = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'won')
            ->sum('prize_won');
        $payoutRatio = $totalRevenue > 0 ? ($totalPrizes / $totalRevenue) * 100 : 0;

        // 6. Customer Segments
        $customerSegments = [
            'vip' => $customerSpending->where('total_spent', '>=', 10000)->count(),
            'premium' => $customerSpending->whereBetween('total_spent', [5000, 9999])->count(),
            'regular' => $customerSpending->whereBetween('total_spent', [1000, 4999])->count(),
            'basic' => $customerSpending->where('total_spent', '<', 1000)->count(),
        ];

        // 7. Revenue by Ticket Type
        $revenueByType = LotteryTicket::selectRaw('
                lottery_tickets.ticket_type,
                COALESCE(SUM(ticket_purchases.total_price), 0) as revenue,
                COUNT(ticket_purchases.id) as sales_count
            ')
            ->leftJoin('ticket_purchases', function($join) use ($dateRange) {
                $join->on('ticket_purchases.lottery_ticket_id', '=', 'lottery_tickets.id')
                    ->whereBetween('ticket_purchases.created_at', [$dateRange['start'], $dateRange['end']])
                    ->whereIn('ticket_purchases.status', ['approved', 'won', 'not_won']);
            })
            ->groupBy('lottery_tickets.ticket_type')
            ->get();

        return [
            'customerLifetimeValue' => [
                'average' => round($avgCLV ?? 0, 2),
                'maximum' => round($maxCLV ?? 0, 2),
                'highValueCount' => $highValueCustomers,
            ],
            'retentionRate' => round($retentionRate, 2),
            'peakHours' => [
                'busiest' => [
                    'hour' => $busiestHour ? $busiestHour->hour : null,
                    'orders' => $busiestHour ? $busiestHour->orders : 0,
                ],
                'hourlyData' => $peakHours->pluck('orders')->toArray(),
                'hourlyLabels' => $peakHours->pluck('hour')->map(fn($h) => sprintf('%02d:00', $h))->toArray(),
            ],
            'bestDay' => $bestDay ? [
                'day' => $bestDay->day,
                'revenue' => $bestDay->revenue,
                'orders' => $bestDay->orders,
            ] : null,
            'dayOfWeekData' => [
                'labels' => $dayOfWeek->pluck('day')->toArray(),
                'revenue' => $dayOfWeek->pluck('revenue')->map(fn($v) => (float)$v)->toArray(),
                'orders' => $dayOfWeek->pluck('orders')->toArray(),
            ],
            'payoutRatio' => [
                'percentage' => round($payoutRatio, 2),
                'totalRevenue' => $totalRevenue,
                'totalPrizes' => $totalPrizes,
            ],
            'customerSegments' => $customerSegments,
            'revenueByType' => [
                'labels' => $revenueByType->pluck('ticket_type')->toArray(),
                'revenue' => $revenueByType->pluck('revenue')->map(fn($v) => (float)$v)->toArray(),
                'sales' => $revenueByType->pluck('sales_count')->map(fn($v) => (int)$v)->toArray(),
            ],
        ];
    }

    private function getAvgTimeBetweenPurchases($dateRange)
    {
        $customersWithMultiple = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select('customer_id', DB::raw('MIN(created_at) as first_purchase, MAX(created_at) as last_purchase, COUNT(*) as count'))
            ->groupBy('customer_id')
            ->having('count', '>', 1)
            ->get();

        if ($customersWithMultiple->isEmpty()) {
            return 'N/A';
        }

        $totalDays = 0;
        foreach ($customersWithMultiple as $customer) {
            $days = $customer->last_purchase->diffInDays($customer->first_purchase);
            if ($days > 0) {
                $totalDays += $days / ($customer->count - 1);
            }
        }

        $avgDays = $totalDays / $customersWithMultiple->count();
        
        if ($avgDays < 1) {
            return 'Less than 1 day';
        } elseif ($avgDays < 7) {
            return round($avgDays, 1) . ' days';
        } elseif ($avgDays < 30) {
            return round($avgDays / 7, 1) . ' weeks';
        } else {
            return round($avgDays / 30, 1) . ' months';
        }
    }

    private function getSecondarySalesData($dateRange)
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $transactions = SecondarySalesTransaction::whereBetween('purchased_at', [$start, $end]);

        $totalThb = (clone $transactions)->sum('amount_thb');
        $totalMmk = (clone $transactions)->sum('amount_mmk');
        $transactionCount = (clone $transactions)->count();
        $wonCount = (clone $transactions)->where('status', 'won')->count();
        $notWonCount = (clone $transactions)->where('status', 'not_won')->count();
        $pendingCount = (clone $transactions)->where('status', 'pending')->count();
        $totalChecked = $wonCount + $notWonCount;
        $winRate = $totalChecked > 0 ? round(($wonCount / $totalChecked) * 100, 1) : 0;

        $collectedThb = SecondarySalesTransaction::whereBetween('purchased_at', [$start, $end])
            ->where('is_paid', true)->sum('amount_thb');
        $collectedMmk = SecondarySalesTransaction::whereBetween('purchased_at', [$start, $end])
            ->where('is_paid', true)->sum('amount_mmk');
        $pendingPaymentThb = SecondarySalesTransaction::whereBetween('purchased_at', [$start, $end])
            ->where('is_paid', false)->sum('amount_thb');
        $pendingPaymentMmk = SecondarySalesTransaction::whereBetween('purchased_at', [$start, $end])
            ->where('is_paid', false)->sum('amount_mmk');

        $wonTransactions = SecondarySalesTransaction::whereBetween('purchased_at', [$start, $end])
            ->where('status', 'won')->sum('prize_won');

        $dailyTrend = SecondarySalesTransaction::whereBetween('purchased_at', [$start, $end])
            ->selectRaw('DATE(purchased_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount_thb) as thb_revenue')
            ->selectRaw('SUM(amount_mmk) as mmk_revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M d'),
                    'count' => $item->count,
                    'thb' => $item->thb_revenue,
                    'mmk' => $item->mmk_revenue,
                ];
            });

        $topBuyers = SecondarySalesTransaction::whereBetween('purchased_at', [$start, $end])
            ->select('customer_name', 'customer_phone')
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(amount_thb) as total_thb')
            ->selectRaw('SUM(amount_mmk) as total_mmk')
            ->groupBy('customer_name', 'customer_phone')
            ->orderByDesc('total_thb')
            ->take(5)
            ->get();

        return [
            'totalThb' => $totalThb,
            'totalMmk' => $totalMmk,
            'transactionCount' => $transactionCount,
            'wonCount' => $wonCount,
            'notWonCount' => $notWonCount,
            'pendingCount' => $pendingCount,
            'winRate' => $winRate,
            'collectedThb' => $collectedThb,
            'collectedMmk' => $collectedMmk,
            'pendingPaymentThb' => $pendingPaymentThb,
            'pendingPaymentMmk' => $pendingPaymentMmk,
            'totalPrizes' => $wonTransactions,
            'dailyTrend' => $dailyTrend,
            'topBuyers' => $topBuyers,
        ];
    }
}
