@extends('layouts.vertical', ['title' => 'Analytics Dashboard'])

@section('css')
<style>
    /* Counter Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card {
        animation: fadeInUp 0.6s ease-out;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
</style>
@endsection

@section('content')
    {{-- Header with Export/Refresh --}}
    <div class="card mb-5">
        <div class="card-body">
            <div class="grid lg:grid-cols-12 grid-cols-1 gap-6 items-center">
                <div class="lg:col-span-8 col-span-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex items-center justify-center size-12 rounded-xl bg-primary/10">
                            <i class="size-6 text-primary" data-lucide="bar-chart-3"></i>
                        </div>
                        <div>
                            <h5 class="text-xl font-bold text-default-900">Business Analytics Dashboard</h5>
                            <p class="text-sm text-default-600">Comprehensive business insights and performance metrics</p>
                        </div>
                    </div>
                    <p class="text-default-700 text-sm leading-relaxed">
                        Monitor revenue, sales, customer behavior, and winning patterns. Track key metrics to grow your lottery business effectively.
                    </p>
                </div>
                <div class="lg:col-span-4 col-span-1">
                    <div class="grid grid-cols-2 gap-3">
                        <button class="btn bg-primary text-white hover:bg-primary/90" type="button" onclick="window.print()">
                            <i class="size-4 me-1" data-lucide="download"></i> Export
                        </button>
                        <button class="btn bg-default-100 text-default-700 hover:bg-default-200" type="button" onclick="window.location.reload()">
                            <i class="size-4 me-1" data-lucide="refresh-cw"></i> Refresh
                        </button>
                    </div>
                    <div class="mt-3 p-3 bg-success/5 rounded-lg border border-success/20">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-default-600">Last Updated</span>
                            <span class="text-xs font-semibold text-success">Just now</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Advanced Filter Section --}}
    <div class="card mb-5">
        <div class="card-body">
            <form method="GET" action="{{ route('analytics.index') }}">
                <div class="grid lg:grid-cols-6 md:grid-cols-3 grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-default-900 mb-2">
                            <i class="size-3.5 me-1" data-lucide="calendar"></i> Time Period
                        </label>
                        <select name="period" class="form-select border-default-300" onchange="this.form.submit()">
                            <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Last Month</option>
                            <option value="3months" {{ $period == '3months' ? 'selected' : '' }}>Last 3 Months</option>
                            <option value="6months" {{ $period == '6months' ? 'selected' : '' }}>Last 6 Months</option>
                            <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Last Year</option>
                        </select>
                    </div>
                    
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-default-900 mb-2">
                            <i class="size-3.5 me-1" data-lucide="search"></i> Quick Search
                        </label>
                        <input type="text" class="form-input border-default-300" placeholder="Search tickets, customers..." />
                    </div>

                    <div class="lg:col-span-3 flex items-end gap-2">
                        <button type="button" onclick="window.location.reload()" class="btn bg-primary text-white flex-1">
                            <i class="size-4 me-1" data-lucide="refresh-cw"></i> Refresh
                        </button>
                        <button type="button" onclick="window.print()" class="btn bg-success text-white flex-1">
                            <i class="size-4 me-1" data-lucide="download"></i> Export
                        </button>
                        <button type="button" class="btn bg-default-150 text-default-700 hover:bg-default-200">
                            <i class="size-4" data-lucide="settings"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main KPI Cards - Compact Design --}}
    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-5 mb-5">
        {{-- Total Users --}}
        <div class="stat-card card bg-gradient-to-br from-primary/10 to-primary/5 border-0 overflow-hidden hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center rounded-xl size-14 bg-primary/20">
                        <i class="size-7 text-primary" data-lucide="users"></i>
                    </div>
                    <span class="px-2.5 py-1 text-xs rounded-full {{ $kpis['userGrowth'] >= 0 ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger' }} flex items-center gap-1">
                        <i class="size-3" data-lucide="{{ $kpis['userGrowth'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                        {{ abs($kpis['userGrowth']) }}%
                    </span>
                </div>
                <h5 class="text-3xl font-bold text-default-900 mb-1">
                    <span class="counter-value" data-target="{{ $kpis['totalUsers'] }}">0</span>
                </h5>
                <p class="text-sm text-default-600 font-medium">Total Users</p>
                <div class="mt-3 pt-3 border-t border-default-200">
                    <p class="text-xs text-default-500">
                        <i class="size-3 me-1" data-lucide="activity"></i>
                        {{ $kpis['totalSessions'] }} active sessions
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="stat-card card bg-gradient-to-br from-success/10 to-success/5 border-0 overflow-hidden hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center rounded-xl size-14 bg-success/20">
                        <span class="text-2xl font-bold text-success">฿</span>
                    </div>
                    <span class="px-2.5 py-1 text-xs rounded-full {{ $kpis['revenueGrowth'] >= 0 ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger' }} flex items-center gap-1">
                        <i class="size-3" data-lucide="{{ $kpis['revenueGrowth'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                        {{ abs($kpis['revenueGrowth']) }}%
                    </span>
                </div>
                <h5 class="text-3xl font-bold text-default-900 mb-1">
                    ฿<span class="counter-value" data-target="{{ $kpis['totalRevenue'] }}" data-format="currency">0</span>
                </h5>
                <p class="text-sm text-default-600 font-medium">Total Revenue</p>
                <div class="mt-3 pt-3 border-t border-default-200">
                    <p class="text-xs text-default-500">
                        <i class="size-3 me-1" data-lucide="trending-up"></i>
                        Avg: ฿{{ number_format($salesData['avgOrderValue'], 2) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Total Sales --}}
        <div class="stat-card card bg-gradient-to-br from-warning/10 to-warning/5 border-0 overflow-hidden hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center rounded-xl size-14 bg-warning/20">
                        <i class="size-7 text-warning" data-lucide="shopping-cart"></i>
                    </div>
                    <span class="px-2.5 py-1 text-xs rounded-full bg-info/20 text-info flex items-center gap-1">
                        <i class="size-3" data-lucide="clock"></i>
                        {{ $salesData['pendingOrders'] }} pending
                    </span>
                </div>
                <h5 class="text-3xl font-bold text-default-900 mb-1">
                    <span class="counter-value" data-target="{{ $salesData['totalSales'] }}">0</span>
                </h5>
                <p class="text-sm text-default-600 font-medium">Total Orders</p>
                <div class="mt-3 pt-3 border-t border-default-200">
                    <p class="text-xs text-default-500">
                        <i class="size-3 me-1" data-lucide="shopping-bag"></i>
                        {{ $salesData['rejectedOrders'] }} rejected
                    </p>
                </div>
            </div>
        </div>

        {{-- Win Rate --}}
        <div class="stat-card card bg-gradient-to-br from-purple-500/10 to-purple-500/5 border-0 overflow-hidden hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center rounded-xl size-14 bg-purple-500/20">
                        <i class="size-7 text-purple-500" data-lucide="trophy"></i>
                    </div>
                    <span class="px-2.5 py-1 text-xs rounded-full bg-purple-500/20 text-purple-600">
                        <i class="size-3" data-lucide="award"></i> Win Rate
                    </span>
                </div>
                <h5 class="text-3xl font-bold text-default-900 mb-1">
                    <span class="counter-value" data-target="{{ $winningData['winRate'] }}">0</span>%
                </h5>
                <p class="text-sm text-default-600 font-medium">Customer Win Rate</p>
                <div class="mt-3 pt-3 border-t border-default-200">
                    <p class="text-xs text-default-500">
                        <i class="size-3 me-1" data-lucide="gift"></i>
                        ฿{{ number_format($winningData['totalPrizes'], 2) }} in prizes
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Access Shortcuts Widget - Minimalist Premium Design --}}
    <div class="card mb-6 overflow-hidden border-0 shadow-sm" style="background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);">
        <div class="card-header bg-transparent border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-lg bg-gray-900">
                        <i class="size-5 text-white" data-lucide="zap"></i>
                    </div>
                    <div>
                        <h6 class="text-base font-semibold text-gray-900 tracking-tight">Quick Access</h6>
                        <p class="text-xs text-gray-500 mt-0.5">Navigate to key areas</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-6">
            <div class="grid lg:grid-cols-5 md:grid-cols-3 grid-cols-2 gap-4">
                {{-- Lottery Management --}}
                <a href="{{ route('tickets.index') }}" class="group relative">
                    <div class="relative p-5 bg-white rounded-xl border border-gray-200 hover:border-gray-900 transition-all duration-300 hover:shadow-lg">
                        <!-- Subtle gradient overlay -->
                        <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-white opacity-0 group-hover:opacity-100 rounded-xl transition-opacity duration-300"></div>
                        
                        <div class="relative z-10">
                            <!-- Icon -->
                            <div class="flex items-center justify-center size-12 rounded-lg bg-gray-100 group-hover:bg-gray-900 transition-all duration-300 mb-3">
                                <i class="size-6 text-gray-700 group-hover:text-white transition-colors duration-300" data-lucide="ticket"></i>
                            </div>
                            
                            <!-- Title -->
                            <h6 class="text-sm font-semibold text-gray-900 mb-1">Lottery</h6>
                            <p class="text-xs text-gray-500">Tickets & draws</p>
                            
                            <!-- Arrow -->
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <i class="size-4 text-gray-900" data-lucide="arrow-up-right"></i>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- Sync Results --}}
                <a href="{{ route('draw_results.syncLatest') }}" class="group relative">
                    <div class="relative p-5 bg-white rounded-xl border border-gray-200 hover:border-gray-900 transition-all duration-300 hover:shadow-lg">
                        <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-white opacity-0 group-hover:opacity-100 rounded-xl transition-opacity duration-300"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center justify-center size-12 rounded-lg bg-gray-100 group-hover:bg-gray-900 transition-all duration-300 mb-3">
                                <i class="size-6 text-gray-700 group-hover:text-white transition-colors duration-300" data-lucide="refresh-cw"></i>
                            </div>
                            <h6 class="text-sm font-semibold text-gray-900 mb-1">Sync Results</h6>
                            <p class="text-xs text-gray-500">Update draws</p>
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <i class="size-4 text-gray-900" data-lucide="arrow-up-right"></i>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- Draw Date Management --}}
                <a href="{{ route('drawinfos.index') }}" class="group relative">
                    <div class="relative p-5 bg-white rounded-xl border border-gray-200 hover:border-gray-900 transition-all duration-300 hover:shadow-lg">
                        <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-white opacity-0 group-hover:opacity-100 rounded-xl transition-opacity duration-300"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center justify-center size-12 rounded-lg bg-gray-100 group-hover:bg-gray-900 transition-all duration-300 mb-3">
                                <i class="size-6 text-gray-700 group-hover:text-white transition-colors duration-300" data-lucide="calendar-days"></i>
                            </div>
                            <h6 class="text-sm font-semibold text-gray-900 mb-1">Draw Dates</h6>
                            <p class="text-xs text-gray-500">Schedule draws</p>
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <i class="size-4 text-gray-900" data-lucide="arrow-up-right"></i>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- Customer Management --}}
                <a href="{{ route('customers.index') }}" class="group relative">
                    <div class="relative p-5 bg-white rounded-xl border border-gray-200 hover:border-gray-900 transition-all duration-300 hover:shadow-lg">
                        <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-white opacity-0 group-hover:opacity-100 rounded-xl transition-opacity duration-300"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center justify-center size-12 rounded-lg bg-gray-100 group-hover:bg-gray-900 transition-all duration-300 mb-3">
                                <i class="size-6 text-gray-700 group-hover:text-white transition-colors duration-300" data-lucide="users-round"></i>
                            </div>
                            <h6 class="text-sm font-semibold text-gray-900 mb-1">Customers</h6>
                            <p class="text-xs text-gray-500">User management</p>
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <i class="size-4 text-gray-900" data-lucide="arrow-up-right"></i>
                            </div>
                        </div>
                    </div>
                </a>

                {{-- Order Management --}}
                <a href="{{ route('purchases.index') }}" class="group relative">
                    <div class="relative p-5 bg-white rounded-xl border border-gray-200 hover:border-gray-900 transition-all duration-300 hover:shadow-lg">
                        <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-white opacity-0 group-hover:opacity-100 rounded-xl transition-opacity duration-300"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center justify-center size-12 rounded-lg bg-gray-100 group-hover:bg-gray-900 transition-all duration-300 mb-3">
                                <i class="size-6 text-gray-700 group-hover:text-white transition-colors duration-300" data-lucide="shopping-cart"></i>
                            </div>
                            <h6 class="text-sm font-semibold text-gray-900 mb-1">Orders</h6>
                            <p class="text-xs text-gray-500">Purchase tracking</p>
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <i class="size-4 text-gray-900" data-lucide="arrow-up-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="grid lg:grid-cols-2 grid-cols-1 gap-6 mb-6">
        {{-- Revenue Trend --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-4 text-success" data-lucide="trending-up"></i>
                    <h6 class="card-title">Revenue & Orders Trend</h6>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <div class="size-3 rounded-full bg-success"></div>
                    <span class="text-default-600">Revenue</span>
                    <div class="size-3 rounded-full bg-primary ml-2"></div>
                    <span class="text-default-600">Orders</span>
                </div>
            </div>
            <div class="card-body">
                <div id="revenueTrendChart" class="apex-charts"></div>
            </div>
        </div>

        {{-- Revenue by Status --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-4 text-primary" data-lucide="pie-chart"></i>
                    <h6 class="card-title">Revenue by Status</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="revenueStatusChart" class="apex-charts"></div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-6 mb-6">
        {{-- Customer Growth --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-4 text-purple-500" data-lucide="users"></i>
                    <h6 class="card-title">Customer Growth</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="customerGrowthChart" class="apex-charts"></div>
            </div>
        </div>

        {{-- Platform Distribution --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-4 text-info" data-lucide="smartphone"></i>
                    <h6 class="card-title">Platform Distribution</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="platformChart" class="apex-charts"></div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="text-center p-3 bg-info/5 rounded-lg">
                        <h5 class="text-xl font-bold text-info">{{ $platformData['totalDevices'] ?? 0 }}</h5>
                        <p class="text-xs text-default-600 mt-1">Total Devices</p>
                    </div>
                    <div class="text-center p-3 bg-success/5 rounded-lg">
                        <h5 class="text-xl font-bold text-success">{{ $platformData['uniqueUsers'] ?? 0 }}</h5>
                        <p class="text-xs text-default-600 mt-1">Unique Users</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gender Distribution --}}
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-4 text-warning" data-lucide="users"></i>
                    <h6 class="card-title">Gender Distribution</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="genderChart" class="apex-charts"></div>
            </div>
        </div>
    </div>

    {{-- Activity Trends --}}
    <div class="card mb-6">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <i class="size-4 text-warning" data-lucide="activity"></i>
                <h6 class="card-title">Activity Trends</h6>
            </div>
            <p class="text-sm text-default-600">User activities and engagement over time</p>
        </div>
        <div class="card-body">
            <div id="activityChart" class="apex-charts"></div>
        </div>
    </div>

    {{-- All Sold Tickets Table --}}
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <i class="size-5 text-primary" data-lucide="ticket"></i>
                <h6 class="card-title">All Sold Tickets</h6>
            </div>
            <span class="text-sm text-default-600">All lottery tickets sold in the selected period</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-default-200">
                <thead class="bg-default-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Ticket</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Sales Count</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Quantity</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-default-200 bg-white">
                    @forelse($productData['topTickets'] as $ticket)
                    <tr class="hover:bg-default-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-default-900">
                                {{ $ticket->lotteryTicket->ticket_number ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-default-500">
                                {{ $ticket->lotteryTicket->ticket_type ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 text-xs bg-primary/10 text-primary rounded-full font-medium">
                                {{ number_format($ticket->sales_count) }} orders
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-default-900">
                            {{ number_format($ticket->total_quantity) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-success text-lg">฿{{ number_format($ticket->total_revenue, 2) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="size-16 text-default-300 mb-3" data-lucide="inbox"></i>
                                <p class="text-default-600 font-medium">No tickets sold yet</p>
                                <p class="text-sm text-default-500 mt-1">Data will appear here once you have sales</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // ============================================
            // COUNTER ANIMATION
            // ============================================
            function formatNumber(value, format = null) {
                const num = Math.round(value);
                if (format === 'currency') {
                    return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                }
                return num.toLocaleString('en-US');
            }

            document.querySelectorAll('.counter-value').forEach(counter => {
                const target = parseFloat(counter.getAttribute('data-target'));
                const format = counter.getAttribute('data-format') || null;
                const duration = 2000;
                const fps = 60;
                const frameCount = Math.ceil((duration / 1000) * fps);
                const step = target / frameCount;
                let current = 0;
                let frame = 0;

                const timer = setInterval(() => {
                    frame++;
                    current += step;
                    
                    if (frame >= frameCount || current >= target) {
                        counter.textContent = formatNumber(target, format);
                        clearInterval(timer);
                    } else {
                        counter.textContent = formatNumber(current, format);
                    }
                }, 1000 / fps);
            });

            // ============================================
            // APEXCHARTS
            // ============================================
            const commonOptions = {
                chart: {
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 3,
                },
                tooltip: {
                    theme: 'dark',
                    style: { fontSize: '12px' }
                }
            };

            // Revenue Trend Chart
            new ApexCharts(document.querySelector("#revenueTrendChart"), {
                ...commonOptions,
                series: [{
                    name: 'Revenue',
                    data: @json($revenueData['trend']['revenue'])
                }, {
                    name: 'Orders',
                    data: @json($revenueData['trend']['orders'])
                }],
                chart: {
                    ...commonOptions.chart,
                    height: 350,
                    type: 'area',
                },
                colors: ['#10b981', '#3b82f6'],
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                    }
                },
                xaxis: {
                    categories: @json($revenueData['trend']['labels']),
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                yaxis: [{
                    title: { text: 'Revenue (฿)', style: { color: '#64748b' } },
                    labels: { style: { colors: '#64748b' } }
                }, {
                    opposite: true,
                    title: { text: 'Orders', style: { color: '#64748b' } },
                    labels: { style: { colors: '#64748b' } }
                }],
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    markers: { radius: 10 }
                },
                dataLabels: { enabled: false }
            }).render();

            // Revenue by Status Chart
            new ApexCharts(document.querySelector("#revenueStatusChart"), {
                ...commonOptions,
                series: @json($revenueData['byStatus']['values']),
                chart: {
                    ...commonOptions.chart,
                    type: 'pie',
                    height: 350
                },
                labels: @json($revenueData['byStatus']['labels']),
                colors: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6'],
                legend: {
                    position: 'bottom',
                    markers: { radius: 10 }
                },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '12px', fontWeight: 600 }
                }
            }).render();

            // Customer Growth Chart
            new ApexCharts(document.querySelector("#customerGrowthChart"), {
                ...commonOptions,
                series: [{
                    name: 'New Customers',
                    data: @json($customerData['newCustomers']['counts'])
                }],
                chart: {
                    ...commonOptions.chart,
                    height: 280,
                    type: 'bar',
                },
                colors: ['#8b5cf6'],
                plotOptions: {
                    bar: { borderRadius: 8, columnWidth: '60%' }
                },
                xaxis: {
                    categories: @json($customerData['newCustomers']['labels']),
                    labels: { style: { colors: '#64748b', fontSize: '11px' } }
                },
                yaxis: {
                    labels: { style: { colors: '#64748b' } }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.5,
                        opacityFrom: 0.85,
                        opacityTo: 0.85,
                    }
                },
                dataLabels: { enabled: false }
            }).render();

            // Platform Chart
            new ApexCharts(document.querySelector("#platformChart"), {
                ...commonOptions,
                series: @json($platformData['deviceDistribution']['values']),
                chart: {
                    ...commonOptions.chart,
                    type: 'donut',
                    height: 250
                },
                labels: @json($platformData['deviceDistribution']['labels']),
                colors: ['#0ea5e9', '#10b981', '#f43f5e'],
                legend: {
                    position: 'bottom',
                    markers: { radius: 10 }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '14px',
                                    fontWeight: 500
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '12px', fontWeight: 600 }
                }
            }).render();

            // Gender Chart
            new ApexCharts(document.querySelector("#genderChart"), {
                ...commonOptions,
                series: @json($customerData['genderDistribution']['values']),
                chart: {
                    ...commonOptions.chart,
                    type: 'donut',
                    height: 280
                },
                labels: @json($customerData['genderDistribution']['labels']),
                colors: ['#3b82f6', '#ec4899', '#94a3b8'],
                legend: {
                    position: 'bottom',
                    markers: { radius: 10 }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '14px'
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '12px', fontWeight: 600 }
                }
            }).render();

            // Activity Chart
            new ApexCharts(document.querySelector("#activityChart"), {
                ...commonOptions,
                series: [{
                    name: 'Activities',
                    data: @json($activityData['activities'])
                }, {
                    name: 'Unique Users',
                    data: @json($activityData['uniqueUsers'])
                }],
                chart: {
                    ...commonOptions.chart,
                    height: 350,
                    type: 'line',
                },
                colors: ['#f97316', '#06b6d4'],
                stroke: { width: [3, 3], curve: 'smooth' },
                markers: {
                    size: 5,
                    hover: { size: 7 }
                },
                xaxis: {
                    categories: @json($activityData['labels']),
                    labels: { style: { colors: '#64748b', fontSize: '12px' } }
                },
                yaxis: {
                    labels: { style: { colors: '#64748b' } }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    markers: { radius: 10 }
                },
                dataLabels: { enabled: false }
            }).render();
        });
    </script>
@endsection
