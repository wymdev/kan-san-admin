<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\TicketPurchase;
use App\Models\LotteryTicket;
use App\Models\DevicePushToken;
use App\Models\ActivityLog;
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
            'avgOrderValue' => round($avgOrderValue, 2),
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
}
