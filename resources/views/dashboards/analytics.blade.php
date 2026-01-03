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

    {{-- Secondary Sales Module --}}
    <div class="card mb-6 overflow-hidden border-0 shadow-sm" style="background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);">
        <div class="card-header bg-transparent border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-lg bg-warning/20">
                        <i class="size-5 text-warning" data-lucide="shopping-bag"></i>
                    </div>
                    <div>
                        <h6 class="text-base font-semibold text-gray-900 tracking-tight">Secondary Sales</h6>
                        <p class="text-xs text-gray-500 mt-0.5">Offline/External lottery sales</p>
                    </div>
                </div>
                <a href="{{ route('secondary-sales.dashboard') }}" class="btn bg-warning/10 text-warning hover:bg-warning/20 btn-sm">
                    <i class="size-4 me-1" data-lucide="external-link"></i> Full Dashboard
                </a>
            </div>
        </div>
        <div class="card-body p-6">
            {{-- Secondary Sales KPI Cards --}}
            <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-4 mb-6">
                {{-- Transactions --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500">Transactions</span>
                        <span class="p-2 bg-primary/10 rounded-lg">
                            <i class="size-4 text-primary" data-lucide="receipt"></i>
                        </span>
                    </div>
                    <h4 class="text-2xl font-bold text-gray-900">{{ number_format($secondarySales['transactionCount']) }}</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ $secondarySales['pendingCount'] }} pending</p>
                </div>

                {{-- Total Revenue THB --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500">Revenue (THB)</span>
                        <span class="p-2 bg-success/10 rounded-lg">
                            <i class="size-4 text-success" data-lucide="banknote"></i>
                        </span>
                    </div>
                    <h4 class="text-2xl font-bold text-success">฿{{ number_format($secondarySales['totalThb'], 0) }}</h4>
                    <p class="text-xs text-gray-500 mt-1">Collected: ฿{{ number_format($secondarySales['collectedThb'], 0) }}</p>
                </div>

                {{-- Total Revenue MMK --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500">Revenue (MMK)</span>
                        <span class="p-2 bg-warning/10 rounded-lg">
                            <i class="size-4 text-warning" data-lucide="coins"></i>
                        </span>
                    </div>
                    <h4 class="text-2xl font-bold text-warning">{{ number_format($secondarySales['totalMmk'], 0) }} K</h4>
                    <p class="text-xs text-gray-500 mt-1">Pending: {{ number_format($secondarySales['pendingPaymentMmk'], 0) }} K</p>
                </div>

                {{-- Win Rate --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500">Win Rate</span>
                        <span class="p-2 bg-purple-100 rounded-lg">
                            <i class="size-4 text-purple-600" data-lucide="trophy"></i>
                        </span>
                    </div>
                    <h4 class="text-2xl font-bold text-purple-600">{{ $secondarySales['winRate'] }}%</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ $secondarySales['wonCount'] }} wins</p>
                </div>
            </div>

            {{-- Secondary Sales Charts & Data --}}
            <div class="grid lg:grid-cols-3 grid-cols-1 gap-6">
                {{-- Daily Trend Chart --}}
                <div class="card col-span-2">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <i class="size-4 text-info" data-lucide="line-chart"></i>
                            <h6 class="card-title">Daily Sales Trend</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="secondarySalesTrendChart" class="apex-charts"></div>
                    </div>
                </div>

                {{-- Top Buyers --}}
                <div class="card">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <i class="size-4 text-success" data-lucide="users"></i>
                            <h6 class="card-title">Top Buyers</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        @forelse($secondarySales['topBuyers'] as $index => $buyer)
                            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 flex items-center justify-center rounded-full {{ $index < 3 ? 'bg-success text-white' : 'bg-gray-100 text-gray-600' }} text-xs font-bold">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $buyer->customer_name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">{{ $buyer->transaction_count }} txn</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($buyer->total_thb > 0)
                                        <p class="text-sm font-bold text-success">฿{{ number_format($buyer->total_thb, 0) }}</p>
                                    @endif
                                    @if($buyer->total_mmk > 0)
                                        <p class="text-xs text-warning">{{ number_format($buyer->total_mmk, 0) }} K</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <i class="size-10 mx-auto mb-2" data-lucide="users"></i>
                                <p class="text-sm">No buyers yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
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

    {{-- ADVANCED BUSINESS INSIGHTS --}}
    <div class="mt-6">
        <h5 class="text-lg font-bold text-default-900 mb-4 flex items-center gap-2">
            <i class="size-5 text-primary" data-lucide="zap"></i>
            Advanced Business Insights
        </h5>
        
        {{-- Row 1: CLV, Retention, Payout Ratio, Conversion --}}
        <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-4 mb-6">
            {{-- Customer Lifetime Value --}}
            <div class="card bg-gradient-to-br from-blue-500/5 to-blue-500/10 border border-blue-200">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center justify-center size-10 rounded-lg bg-blue-500/20">
                            <i class="size-5 text-blue-600" data-lucide="gem"></i>
                        </div>
                        <span class="text-xs text-default-500">CLV</span>
                    </div>
                    <h4 class="text-2xl font-bold text-blue-600">฿{{ number_format($advancedInsights['customerLifetimeValue']['average'], 0) }}</h4>
                    <p class="text-sm text-default-600 mt-1">Avg. Customer Lifetime Value</p>
                    <div class="mt-3 pt-3 border-t border-blue-200/50 flex justify-between text-xs">
                        <span class="text-default-500">Max: ฿{{ number_format($advancedInsights['customerLifetimeValue']['maximum'], 0) }}</span>
                        <span class="text-blue-600">{{ $advancedInsights['customerLifetimeValue']['highValueCount'] }} VIP customers</span>
                    </div>
                </div>
            </div>

            {{-- Retention Rate --}}
            <div class="card bg-gradient-to-br from-green-500/5 to-green-500/10 border border-green-200">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center justify-center size-10 rounded-lg bg-green-500/20">
                            <i class="size-5 text-green-600" data-lucide="repeat"></i>
                        </div>
                        <span class="text-xs text-default-500">Retention</span>
                    </div>
                    <h4 class="text-2xl font-bold text-green-600">{{ $advancedInsights['retentionRate'] }}%</h4>
                    <p class="text-sm text-default-600 mt-1">Repeat Purchase Rate</p>
                    <div class="mt-3 pt-3 border-t border-green-200/50">
                        <div class="w-full bg-green-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $advancedInsights['retentionRate'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Prize Payout Ratio --}}
            <div class="card bg-gradient-to-br from-purple-500/5 to-purple-500/10 border border-purple-200">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center justify-center size-10 rounded-lg bg-purple-500/20">
                            <i class="size-5 text-purple-600" data-lucide="percent"></i>
                        </div>
                        <span class="text-xs text-default-500">Payout</span>
                    </div>
                    <h4 class="text-2xl font-bold text-purple-600">{{ $advancedInsights['payoutRatio']['percentage'] }}%</h4>
                    <p class="text-sm text-default-600 mt-1">Prize Payout Ratio</p>
                    <div class="mt-3 pt-3 border-t border-purple-200/50 text-xs text-default-500">
                        <span>Paid: ฿{{ number_format($advancedInsights['payoutRatio']['totalPrizes'], 0) }} / ฿{{ number_format($advancedInsights['payoutRatio']['totalRevenue'], 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Peak Hours, Customer Segments --}}
        <div class="grid lg:grid-cols-3 grid-cols-1 gap-6 mb-6">
            {{-- Peak Hours Chart --}}
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-2">
                        <i class="size-4 text-info" data-lucide="clock"></i>
                        <h6 class="card-title">Peak Hours</h6>
                    </div>
                    <p class="text-xs text-default-500">Busiest: {{ $advancedInsights['peakHours']['busiest']['hour'] ? sprintf('%02d:00', $advancedInsights['peakHours']['busiest']['hour']) : 'N/A' }} ({{ $advancedInsights['peakHours']['busiest']['orders'] }} orders)</p>
                </div>
                <div class="card-body">
                    <div id="peakHoursChart" class="apex-charts"></div>
                </div>
            </div>

            {{-- Customer Segments --}}
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-2">
                        <i class="size-4 text-primary" data-lucide="users"></i>
                        <h6 class="card-title">Customer Segments</h6>
                    </div>
                    <p class="text-xs text-default-500">By spending</p>
                </div>
                <div class="card-body">
                    @php
                        $segments = [
                            ['label' => 'VIP (฿10k+)', 'count' => $advancedInsights['customerSegments']['vip'], 'color' => '#8b5cf6'],
                            ['label' => 'Premium (฿5k-10k)', 'count' => $advancedInsights['customerSegments']['premium'], 'color' => '#3b82f6'],
                            ['label' => 'Regular (฿1k-5k)', 'count' => $advancedInsights['customerSegments']['regular'], 'color' => '#10b981'],
                            ['label' => 'Basic (<฿1k)', 'count' => $advancedInsights['customerSegments']['basic'], 'color' => '#f59e0b'],
                        ];
                        $totalSegmented = collect($segments)->sum('count');
                    @endphp
                    @if($totalSegmented > 0)
                        @foreach($segments as $seg)
                        <div class="flex items-center justify-between text-sm py-2 border-b border-default-100 last:border-0">
                            <span class="flex items-center gap-2">
                                <span class="size-2 rounded-full" style="background-color: {{ $seg['color'] }}"></span>
                                {{ $seg['label'] }}
                            </span>
                            <span class="font-semibold">{{ $seg['count'] }}</span>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-6 text-default-500">
                            <p class="text-sm">No data</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Best Day Performance --}}
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-2">
                        <i class="size-4 text-success" data-lucide="calendar-check"></i>
                        <h6 class="card-title">Best Day Performance</h6>
                    </div>
                </div>
                <div class="card-body">
                    @if($advancedInsights['bestDay'])
                    <div class="text-center p-4 bg-success/5 rounded-xl border border-success/20 mb-4">
                        <p class="text-sm text-default-600 mb-1">Best Day</p>
                        <p class="text-2xl font-bold text-success">{{ $advancedInsights['bestDay']['day'] }}</p>
                        <p class="text-sm text-default-500">฿{{ number_format($advancedInsights['bestDay']['revenue'], 0) }} revenue</p>
                    </div>
                    @else
                    <div class="text-center py-6 text-default-500">
                        <i class="size-10 mx-auto mb-2 text-default-300" data-lucide="calendar"></i>
                        <p class="text-sm">No data available</p>
                    </div>
                    @endif
                    <div class="space-y-2">
                        @if(!empty($advancedInsights['dayOfWeekData']['labels']))
                            @foreach($advancedInsights['dayOfWeekData']['labels'] as $index => $day)
                            <div class="flex items-center justify-between text-sm">
                                <span>{{ $day }}</span>
                                <span class="font-medium">฿{{ number_format($advancedInsights['dayOfWeekData']['revenue'][$index] ?? 0, 0) }}</span>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Revenue by Ticket Type --}}
        <div class="grid lg:grid-cols-2 grid-cols-1 gap-6 mb-6">
            {{-- Revenue by Ticket Type --}}
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-2">
                        <i class="size-4 text-primary" data-lucide="tags"></i>
                        <h6 class="card-title">Revenue by Ticket Type</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div id="ticketTypeChart" class="apex-charts"></div>
                </div>
            </div>

            {{-- Revenue Summary --}}
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-2">
                        <i class="size-4 text-success" data-lucide="dollar-sign"></i>
                        <h6 class="card-title">Revenue Summary</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center p-4 bg-primary/5 rounded-xl border border-primary/20 mb-4">
                        <p class="text-sm text-default-600 mb-1">Total Revenue</p>
                        <p class="text-3xl font-bold text-primary">฿{{ number_format($advancedInsights['payoutRatio']['totalRevenue'], 0) }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-3 bg-success/5 rounded-lg">
                            <p class="text-sm text-default-600">Prizes Paid</p>
                            <p class="text-xl font-bold text-success">฿{{ number_format($advancedInsights['payoutRatio']['totalPrizes'], 0) }}</p>
                        </div>
                        <div class="text-center p-3 bg-info/5 rounded-lg">
                            <p class="text-sm text-default-600">Payout Ratio</p>
                            <p class="text-xl font-bold text-info">{{ $advancedInsights['payoutRatio']['percentage'] }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 4: Quick Stats Summary --}}
        <div class="card bg-gradient-to-r from-gray-900 to-gray-800 text-white">
            <div class="card-body">
                <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-6">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center size-12 rounded-xl bg-white/10">
                            <i class="size-6 text-white" data-lucide="hourglass"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Busiest Hour</p>
                            <p class="text-xl font-bold">{{ $advancedInsights['peakHours']['busiest']['hour'] ? sprintf('%02d:00', $advancedInsights['peakHours']['busiest']['hour']) : 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $advancedInsights['peakHours']['busiest']['orders'] }} orders</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center size-12 rounded-xl bg-white/10">
                            <i class="size-6 text-white" data-lucide="calendar"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Best Day This Period</p>
                            <p class="text-xl font-bold">{{ $advancedInsights['bestDay']['day'] ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">฿{{ number_format($advancedInsights['bestDay']['revenue'] ?? 0, 0) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center size-12 rounded-xl bg-white/10">
                            <i class="size-6 text-white" data-lucide="trending-up"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Highest Single Ticket Revenue</p>
                            <p class="text-xl font-bold">฿{{ number_format($productData['topTickets']->max('total_revenue') ?? 0, 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>
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

            // ============================================
            // ADVANCED INSIGHTS CHARTS
            // ============================================

            // Peak Hours Chart
            new ApexCharts(document.querySelector("#peakHoursChart"), {
                ...commonOptions,
                series: [{
                    name: 'Orders',
                    data: @json($advancedInsights['peakHours']['hourlyData'])
                }],
                chart: {
                    ...commonOptions.chart,
                    height: 250,
                    type: 'bar',
                },
                colors: ['#0ea5e9'],
                plotOptions: {
                    bar: { borderRadius: 4, columnWidth: '60%' }
                },
                xaxis: {
                    categories: @json($advancedInsights['peakHours']['hourlyLabels']),
                    labels: { style: { colors: '#64748b', fontSize: '10px' }, rotate: -45 }
                },
                yaxis: {
                    labels: { style: { colors: '#64748b' } }
                },
                dataLabels: { enabled: false }
            }).render();

            // Ticket Type Revenue Chart
            new ApexCharts(document.querySelector("#ticketTypeChart"), {
                ...commonOptions,
                series: @json($advancedInsights['revenueByType']['revenue']),
                chart: {
                    ...commonOptions.chart,
                    type: 'pie',
                    height: 320
                },
                labels: @json($advancedInsights['revenueByType']['labels']),
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                legend: {
                    position: 'bottom',
                    markers: { radius: 10 }
                },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '12px', fontWeight: 600 }
                }
            }).render();

            // ============================================
            // SECONDARY SALES CHART
            // ============================================
            
            @if($secondarySales['dailyTrend']->isNotEmpty())
            new ApexCharts(document.querySelector("#secondarySalesTrendChart"), {
                ...commonOptions,
                series: [
                    {
                        name: 'THB',
                        data: @json($secondarySales['dailyTrend']->pluck('thb')->toArray())
                    },
                    {
                        name: 'MMK',
                        data: @json($secondarySales['dailyTrend']->pluck('mmk')->toArray())
                    },
                    {
                        name: 'Transactions',
                        data: @json($secondarySales['dailyTrend']->pluck('count')->toArray()),
                        type: 'bar',
                        yAxisIndex: 1
                    }
                ],
                chart: {
                    ...commonOptions.chart,
                    height: 280,
                    type: 'line',
                },
                colors: ['#10b981', '#f59e0b', '#3b82f6'],
                stroke: { width: [3, 3, 0], curve: 'smooth' },
                markers: { size: 4, hover: { size: 6 } },
                plotOptions: {
                    bar: { columnWidth: '40%', borderRadius: 4, distributed: true }
                },
                xaxis: {
                    categories: @json($secondarySales['dailyTrend']->pluck('date')->toArray()),
                    labels: { style: { colors: '#64748b', fontSize: '11px' } }
                },
                yaxis: [
                    {
                        title: { text: 'Revenue', style: { color: '#64748b' } },
                        labels: { style: { colors: '#64748b' } }
                    },
                    {
                        opposite: true,
                        title: { text: 'Transactions', style: { color: '#64748b' } },
                        labels: { style: { colors: '#64748b' } }
                    }
                ],
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    markers: { radius: 10 }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    theme: 'dark'
                },
                dataLabels: { enabled: false }
            }).render();
            @endif
        });
    </script>
@endsection
