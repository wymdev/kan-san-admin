<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\TicketPurchase;
use App\Models\DevicePushToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        
        // Key Metrics
        $metrics = $this->getKeyMetrics($dateRange);
        
        // Charts Data
        $salesTrend = $this->getSalesTrendData($dateRange);
        $winLossDistribution = $this->getWinLossDistribution($dateRange);
        $customerGrowth = $this->getCustomerGrowthData($dateRange);
        $topCustomers = $this->getTopCustomers($dateRange);
        $revenueByStatus = $this->getRevenueByStatus($dateRange);
        $deviceDistribution = $this->getDeviceDistribution();
        $genderDistribution = $this->getGenderDistribution($dateRange);
        $ageDistribution = $this->getAgeDistribution($dateRange);
        $purchaseFrequency = $this->getPurchaseFrequencyData($dateRange);
        $winningStats = $this->getWinningStatistics($dateRange);
        
        return view('dashboards.customer-analytics', compact(
            'metrics',
            'salesTrend',
            'winLossDistribution',
            'customerGrowth',
            'topCustomers',
            'revenueByStatus',
            'deviceDistribution',
            'genderDistribution',
            'ageDistribution',
            'purchaseFrequency',
            'winningStats',
            'dateRange'
        ));
    }

    private function getDateRange(Request $request)
    {
        $period = $request->get('period', 'month');
        
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
            case 'custom':
                $start = Carbon::parse($request->get('start_date', Carbon::now()->subMonth()))->startOfDay();
                $end = Carbon::parse($request->get('end_date', Carbon::now()))->endOfDay();
                break;
            default:
                $start = Carbon::now()->subMonth()->startOfDay();
                $end = Carbon::now();
        }
        
        return ['start' => $start, 'end' => $end, 'period' => $period];
    }

    private function getKeyMetrics($dateRange)
    {
        $currentPeriod = [
            'start' => $dateRange['start'],
            'end' => $dateRange['end']
        ];
        
        // Calculate previous period with same duration
        $durationInDays = $dateRange['end']->diffInDays($dateRange['start']);
        $previousPeriod = [
            'start' => $dateRange['start']->copy()->subDays($durationInDays)->startOfDay(),
            'end' => $dateRange['start']->copy()->subSeconds(1)
        ];

        // Total Customers (ALL customers ever created up to current period end)
        $totalCustomers = Customer::where('created_at', '<=', $currentPeriod['end'])->count();
        $prevTotalCustomers = Customer::where('created_at', '<=', $previousPeriod['end'])->count();
        $customerGrowth = $prevTotalCustomers > 0 ? (($totalCustomers - $prevTotalCustomers) / $prevTotalCustomers) * 100 : 0;

        // Total Sales (only approved, won, not_won)
        $totalSalesRaw = TicketPurchase::whereBetween('created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->sum('total_price');
        
        $prevSales = TicketPurchase::whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->sum('total_price');
        $salesGrowth = $prevSales > 0 ? (($totalSalesRaw - $prevSales) / $prevSales) * 100 : 0;

        // Total Orders (only approved, won, not_won)
        $totalOrders = TicketPurchase::whereBetween('created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->count();
        $prevOrders = TicketPurchase::whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->count();
        $ordersGrowth = $prevOrders > 0 ? (($totalOrders - $prevOrders) / $prevOrders) * 100 : 0;

        // Win Rate (only from checked orders: won + not_won)
        $wonOrders = TicketPurchase::whereBetween('created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->where('status', 'won')
            ->count();
        $checkedOrders = TicketPurchase::whereBetween('created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->whereIn('status', ['won', 'not_won'])
            ->count();
        $winRate = $checkedOrders > 0 ? ($wonOrders / $checkedOrders) * 100 : 0;

        // Average Order Value
        $avgOrderValue = $totalOrders > 0 ? $totalSalesRaw / $totalOrders : 0;

        // Active Customers (customers who made purchase in current period)
        $activeCustomers = TicketPurchase::whereBetween('created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->distinct('customer_id')
            ->count('customer_id');

        // Pending Approvals (all pending, not just in period)
        $pendingApprovals = TicketPurchase::where('status', 'pending')->count();

        // Total Prize Won (sum of prize_won column)
        $totalPrizeWon = TicketPurchase::whereBetween('created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->where('status', 'won')
            ->sum('prize_won');

        return [
            'totalCustomers' => $totalCustomers,
            'customerGrowth' => round($customerGrowth, 2),
            'totalSales' => number_format($totalSalesRaw, 2),
            'totalSalesRaw' => round($totalSalesRaw), // Raw number for counter animation
            'salesGrowth' => round($salesGrowth, 2),
            'totalOrders' => $totalOrders,
            'ordersGrowth' => round($ordersGrowth, 2),
            'winRate' => round($winRate, 2),
            'avgOrderValue' => number_format($avgOrderValue, 2),
            'activeCustomers' => $activeCustomers,
            'pendingApprovals' => $pendingApprovals,
            'totalPrizeWon' => number_format($totalPrizeWon, 2),
        ];
    }

    private function getSalesTrendData($dateRange)
    {
        $groupBy = $this->getGroupByFormat($dateRange);
        
        $data = TicketPurchase::selectRaw("
                DATE_FORMAT(created_at, '{$groupBy}') as period,
                SUM(total_price) as total_sales,
                COUNT(*) as total_orders
            ")
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'labels' => $data->pluck('period')->toArray(),
            'sales' => $data->pluck('total_sales')->map(fn($v) => (float)$v)->toArray(),
            'orders' => $data->pluck('total_orders')->toArray(),
        ];
    }

    private function getWinLossDistribution($dateRange)
    {
        $wonCount = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'won')
            ->count();
        
        $lostCount = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'not_won')
            ->count();

        return [
            'labels' => ['Won', 'Not Won'],
            'counts' => [$wonCount, $lostCount],
        ];
    }

    private function getCustomerGrowthData($dateRange)
    {
        $groupBy = $this->getGroupByFormat($dateRange);
        
        $data = Customer::selectRaw("
                DATE_FORMAT(created_at, '{$groupBy}') as period,
                COUNT(*) as new_customers
            ")
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Calculate cumulative - should start from beginning of database, not just this period
        $customersBeforePeriod = Customer::where('created_at', '<', $dateRange['start'])->count();
        $cumulative = $customersBeforePeriod;
        $cumulativeData = $data->map(function($item) use (&$cumulative) {
            $cumulative += $item->new_customers;
            return $cumulative;
        });

        return [
            'labels' => $data->pluck('period')->toArray(),
            'new' => $data->pluck('new_customers')->toArray(),
            'cumulative' => $cumulativeData->toArray(),
        ];
    }

    private function getTopCustomers($dateRange)
    {
        $topCustomers = Customer::select('customers.id', 'customers.full_name', 'customers.email', 'customers.phone_number')
            ->selectRaw('COUNT(ticket_purchases.id) as total_purchases')
            ->selectRaw('SUM(ticket_purchases.total_price) as total_spent')
            ->selectRaw('SUM(CASE WHEN ticket_purchases.status = "won" THEN 1 ELSE 0 END) as total_wins')
            ->selectRaw('SUM(CASE WHEN ticket_purchases.status IN ("won", "not_won") THEN 1 ELSE 0 END) as checked_purchases')
            ->join('ticket_purchases', 'customers.id', '=', 'ticket_purchases.customer_id')
            ->whereBetween('ticket_purchases.created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('ticket_purchases.status', ['approved', 'won', 'not_won'])
            ->groupBy('customers.id', 'customers.full_name', 'customers.email', 'customers.phone_number')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return $topCustomers->map(function($customer) {
            $winRate = $customer->checked_purchases > 0 ? 
                round(($customer->total_wins / $customer->checked_purchases) * 100, 2) : 0;
            
            return [
                'id' => $customer->id,
                'name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone_number,
                                'purchases' => $customer->total_purchases,
                'spent' => number_format($customer->total_spent, 2),
                'wins' => $customer->total_wins,
                'win_rate' => $winRate
            ];
        });
    }

    private function getRevenueByStatus($dateRange)
    {
        $data = TicketPurchase::selectRaw("
                status,
                SUM(total_price) as revenue
            ")
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('status')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'won', 'not_won', 'rejected')")
            ->get();

        $statusLabels = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'won' => 'Won',
            'not_won' => 'Not Won',
            'rejected' => 'Rejected'
        ];

        return [
            'labels' => $data->pluck('status')->map(fn($s) => $statusLabels[$s] ?? ucfirst(str_replace('_', ' ', $s)))->toArray(),
            'revenue' => $data->pluck('revenue')->map(fn($v) => (float)$v)->toArray(),
        ];
    }

    private function getDeviceDistribution()
    {
        $data = DevicePushToken::selectRaw("
                COALESCE(platform, 'Unknown') as platform,
                COUNT(*) as count
            ")
            ->where('is_active', true)
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->get();

        $platformLabels = [
            'ios' => 'iOS',
            'android' => 'Android',
            'web' => 'Web'
        ];

        return [
            'labels' => $data->pluck('platform')->map(fn($p) => $platformLabels[strtolower($p)] ?? ucfirst($p))->toArray(),
            'counts' => $data->pluck('count')->toArray(),
        ];
    }

    private function getGenderDistribution($dateRange)
    {
        // Get customers who have made purchases in the period
        $customerIds = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->distinct('customer_id')
            ->pluck('customer_id');

        if ($customerIds->isEmpty()) {
            return ['labels' => [], 'counts' => []];
        }

        $data = Customer::selectRaw("
                COALESCE(gender, 'Unknown') as gender,
                COUNT(*) as count
            ")
            ->whereIn('id', $customerIds)
            ->groupBy('gender')
            ->get();

        $genderLabels = [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            'unknown' => 'Unknown'
        ];

        return [
            'labels' => $data->pluck('gender')->map(fn($g) => $genderLabels[strtolower($g)] ?? ucfirst($g))->toArray(),
            'counts' => $data->pluck('count')->toArray(),
        ];
    }

    private function getAgeDistribution($dateRange)
    {
        // Get customers who have made purchases in the period
        $customerIds = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['approved', 'won', 'not_won'])
            ->distinct('customer_id')
            ->pluck('customer_id');

        if ($customerIds->isEmpty()) {
            return ['labels' => ['18-25', '26-35', '36-45', '46-55', '56+'], 'counts' => [0, 0, 0, 0, 0]];
        }

        $customers = Customer::selectRaw("
                TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age
            ")
            ->whereIn('id', $customerIds)
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

        return [
            'labels' => array_keys($ageGroups),
            'counts' => array_values($ageGroups),
        ];
    }

    private function getPurchaseFrequencyData($dateRange)
    {
        $data = Customer::selectRaw("
                customers.id,
                COUNT(ticket_purchases.id) as purchase_count
            ")
            ->join('ticket_purchases', 'customers.id', '=', 'ticket_purchases.customer_id')
            ->whereBetween('ticket_purchases.created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('ticket_purchases.status', ['approved', 'won', 'not_won'])
            ->groupBy('customers.id')
            ->having('purchase_count', '>', 0)
            ->get();

        $frequency = [
            '1-2' => 0,
            '3-5' => 0,
            '6-10' => 0,
            '11-20' => 0,
            '20+' => 0
        ];

        foreach ($data as $customer) {
            $count = $customer->purchase_count;
            if ($count >= 1 && $count <= 2) $frequency['1-2']++;
            elseif ($count >= 3 && $count <= 5) $frequency['3-5']++;
            elseif ($count >= 6 && $count <= 10) $frequency['6-10']++;
            elseif ($count >= 11 && $count <= 20) $frequency['11-20']++;
            elseif ($count > 20) $frequency['20+']++;
        }

        return [
            'labels' => array_keys($frequency),
            'counts' => array_values($frequency),
        ];
    }

    private function getWinningStatistics($dateRange)
    {
        // Only count checked tickets (won + not_won)
        $totalChecked = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereIn('status', ['won', 'not_won'])
            ->count();

        $totalWon = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'won')
            ->count();

        $totalLost = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'not_won')
            ->count();

        $totalPrizes = TicketPurchase::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'won')
            ->sum('prize_won');

        $avgPrize = $totalWon > 0 ? $totalPrizes / $totalWon : 0;

        $winRate = $totalChecked > 0 ? round(($totalWon / $totalChecked) * 100, 2) : 0;

        $biggestWin = TicketPurchase::with('customer')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'won')
            ->orderByDesc('prize_won')
            ->first();

        return [
            'totalChecked' => $totalChecked,
            'totalWon' => $totalWon,
            'totalLost' => $totalLost,
            'winRate' => $winRate,
            'totalPrizes' => number_format($totalPrizes, 2),
            'avgPrize' => number_format($avgPrize, 2),
            'biggestWin' => $biggestWin ? [
                'customer' => $biggestWin->customer->full_name ?? 'N/A',
                'amount' => number_format($biggestWin->prize_won, 2),
                'order' => $biggestWin->order_number,
            ] : null,
        ];
    }

    private function getGroupByFormat($dateRange)
    {
        $days = $dateRange['end']->diffInDays($dateRange['start']);
        
        if ($days <= 1) {
            return '%Y-%m-%d %H:00'; // Hourly for today
        } elseif ($days <= 31) {
            return '%Y-%m-%d'; // Daily
        } elseif ($days <= 90) {
            return '%Y Week %u'; // Weekly
        } else {
            return '%Y-%m'; // Monthly
        }
    }

    public function export(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $type = $request->get('type', 'customers');

        switch ($type) {
            case 'customers':
                return $this->exportCustomers($dateRange);
            case 'sales':
                return $this->exportSales($dateRange);
            case 'winners':
                return $this->exportWinners($dateRange);
            default:
                return back()->with('error', 'Invalid export type');
        }
    }

    private function exportCustomers($dateRange)
    {
        $customers = Customer::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->with(['pushTokens'])
            ->get();

        $filename = 'customers_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Gender', 'DOB', 'Registered', 'Active Devices']);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->full_name,
                    $customer->email,
                    $customer->phone_number,
                    $customer->gender ?? 'N/A',
                    $customer->dob ? $customer->dob->format('Y-m-d') : 'N/A',
                    $customer->created_at->format('Y-m-d H:i:s'),
                    $customer->pushTokens->where('is_active', true)->count(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportSales($dateRange)
    {
        $sales = TicketPurchase::with(['customer', 'lotteryTicket'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'sales_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order Number', 'Customer', 'Email', 'Phone', 'Ticket Number', 'Quantity', 'Total Price', 'Status', 'Prize Won', 'Created At']);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->order_number,
                    $sale->customer->full_name ?? 'N/A',
                    $sale->customer->email ?? 'N/A',
                    $sale->customer->phone_number ?? 'N/A',
                    $sale->lotteryTicket->ticket_number ?? 'N/A',
                    $sale->quantity,
                    number_format($sale->total_price, 2),
                    ucfirst(str_replace('_', ' ', $sale->status)),
                    $sale->prize_won ? number_format($sale->prize_won, 2) : '0.00',
                    $sale->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportWinners($dateRange)
    {
        $winners = TicketPurchase::with(['customer', 'lotteryTicket'])
            ->where('status', 'won')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->orderByDesc('prize_won')
            ->get();

        $filename = 'winners_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($winners) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Rank', 'Order Number', 'Customer', 'Email', 'Phone', 'Ticket Number', 'Prize Won', 'Won At']);

            $rank = 1;
            foreach ($winners as $winner) {
                fputcsv($file, [
                    $rank++,
                    $winner->order_number,
                    $winner->customer->full_name ?? 'N/A',
                    $winner->customer->email ?? 'N/A',
                    $winner->customer->phone_number ?? 'N/A',
                    $winner->lotteryTicket->ticket_number ?? 'N/A',
                    number_format($winner->prize_won, 2),
                    $winner->checked_at ? $winner->checked_at->format('Y-m-d H:i:s') : $winner->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}