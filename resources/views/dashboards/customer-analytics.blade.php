@extends('layouts.vertical', ['title' => 'Customer Analytics'])

@section('css')
@endsection

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Dashboards', 'title' => 'Customer Analytics'])

    {{-- Overview Card --}}
    <div class="card mb-5">
        <div class="card-body">
            <div class="grid lg:grid-cols-12 grid-cols-1 gap-6 items-center">
                <div class="lg:col-span-8 col-span-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex items-center justify-center size-12 rounded-xl bg-primary/10">
                            <i class="size-6 text-primary" data-lucide="bar-chart-3"></i>
                        </div>
                        <div>
                            <h5 class="text-xl font-bold text-default-900">Customer Analytics Dashboard</h5>
                            <p class="text-sm text-default-600">Real-time insights and performance metrics</p>
                        </div>
                    </div>
                    <p class="text-default-700 text-sm leading-relaxed">
                        Track customer behavior, sales performance, and winning patterns. Monitor customer lifetime value, engagement metrics, and identify trends to grow your business.
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
            <form method="GET" action="{{ route('analytics.customers') }}">
                <div class="grid lg:grid-cols-6 md:grid-cols-3 grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-default-900 mb-2">
                            <i class="size-3.5 me-1" data-lucide="calendar"></i> Time Period
                        </label>
                        <select name="period" class="form-select border-default-300" onchange="this.form.submit()">
                            <option value="today" {{ $dateRange['period'] == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ $dateRange['period'] == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="month" {{ $dateRange['period'] == 'month' ? 'selected' : '' }}>Last Month</option>
                            <option value="3months" {{ $dateRange['period'] == '3months' ? 'selected' : '' }}>Last 3 Months</option>
                            <option value="6months" {{ $dateRange['period'] == '6months' ? 'selected' : '' }}>Last 6 Months</option>
                            <option value="year" {{ $dateRange['period'] == 'year' ? 'selected' : '' }}>Last Year</option>
                        </select>
                    </div>
                    
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-default-900 mb-2">
                            <i class="size-3.5 me-1" data-lucide="search"></i> Quick Search
                        </label>
                        <input type="text" class="form-input border-default-300" placeholder="Search customers, orders..." />
                    </div>

                    <div class="lg:col-span-3 flex items-end gap-2">
                        <button type="button" onclick="window.location.reload()" class="btn bg-primary text-white flex-1">
                            <i class="size-4 me-1" data-lucide="refresh-cw"></i> Refresh
                        </button>
                        <div class="hs-dropdown relative inline-flex flex-1">
                            <button class="hs-dropdown-toggle btn bg-success text-white w-full" type="button">
                                <i class="size-4 me-1" data-lucide="download"></i> Export
                            </button>
                            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-48" role="menu">
                                <a class="flex items-center gap-x-3.5 py-2 px-3 text-sm hover:bg-default-100" href="{{ route('analytics.customers.export', ['type' => 'customers', 'period' => $dateRange['period']]) }}">
                                    <i class="size-4" data-lucide="users"></i> Export Customers
                                </a>
                                <a class="flex items-center gap-x-3.5 py-2 px-3 text-sm hover:bg-default-100" href="{{ route('analytics.customers.export', ['type' => 'sales', 'period' => $dateRange['period']]) }}">
                                    <i class="size-4" data-lucide="shopping-cart"></i> Export Sales
                                </a>
                                <a class="flex items-center gap-x-3.5 py-2 px-3 text-sm hover:bg-default-100" href="{{ route('analytics.customers.export', ['type' => 'winners', 'period' => $dateRange['period']]) }}">
                                    <i class="size-4" data-lucide="trophy"></i> Export Winners
                                </a>
                            </div>
                        </div>
                        <button type="button" class="btn bg-default-150 text-default-700 hover:bg-default-200">
                            <i class="size-4" data-lucide="settings"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main KPI Cards --}}
    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-5 mb-5">
        <div class="card bg-gradient-to-br from-primary/10 to-primary/5 border-0 overflow-hidden hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center rounded-xl size-14 bg-primary/20">
                        <i class="size-7 text-primary" data-lucide="users"></i>
                    </div>
                    <span class="px-2.5 py-1 text-xs rounded-full {{ $metrics['customerGrowth'] >= 0 ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger' }} flex items-center gap-1">
                        <i class="size-3" data-lucide="{{ $metrics['customerGrowth'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                        {{ abs($metrics['customerGrowth']) }}%
                    </span>
                </div>
                <h5 class="text-3xl font-bold text-default-900 mb-1">
                    <span class="counter-value" data-target="{{ $metrics['totalCustomers'] ?? 0 }}">0</span>
                </h5>
                <p class="text-sm text-default-600 font-medium">Total Customers</p>
                <div class="mt-3 pt-3 border-t border-default-200">
                    <p class="text-xs text-default-500">
                        <i class="size-3 me-1" data-lucide="activity"></i>
                        {{ $metrics['activeCustomers'] }} active this period
                    </p>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-success/10 to-success/5 border-0 overflow-hidden hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center rounded-xl size-14 bg-success/20">
                        <span class="text-2xl font-bold text-success">฿</span>
                    </div>
                    <span class="px-2.5 py-1 text-xs rounded-full {{ $metrics['salesGrowth'] >= 0 ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger' }} flex items-center gap-1">
                        <i class="size-3" data-lucide="{{ $metrics['salesGrowth'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                        {{ abs($metrics['salesGrowth']) }}%
                    </span>
                </div>
                <h5 class="text-3xl font-bold text-default-900 mb-1">
                    ฿<span class="counter-value" data-target="{{ $metrics['totalSalesRaw'] }}" data-format="currency">0</span>
                </h5>
                <p class="text-sm text-default-600 font-medium">Total Revenue</p>
                <div class="mt-3 pt-3 border-t border-default-200">
                    <p class="text-xs text-default-500">
                        <i class="size-3 me-1" data-lucide="trending-up"></i>
                        Avg: ฿{{ $metrics['avgOrderValue'] }} per order
                    </p>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-warning/10 to-warning/5 border-0 overflow-hidden hover:shadow-lg transition-shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center rounded-xl size-14 bg-warning/20">
                        <i class="size-7 text-warning" data-lucide="shopping-cart"></i>
                    </div>
                    <span class="px-2.5 py-1 text-xs rounded-full {{ $metrics['ordersGrowth'] >= 0 ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger' }} flex items-center gap-1">
                        <i class="size-3" data-lucide="{{ $metrics['ordersGrowth'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                        {{ abs($metrics['ordersGrowth']) }}%
                    </span>
                </div>
                <h5 class="text-3xl font-bold text-default-900 mb-1">
                    <span class="counter-value" data-target="{{ $metrics['totalOrders'] }}">0</span>
                </h5>
                <p class="text-sm text-default-600 font-medium">Total Orders</p>
                <div class="mt-3 pt-3 border-t border-default-200">
                    <p class="text-xs text-default-500">
                        <i class="size-3 me-1" data-lucide="clock"></i>
                        {{ $metrics['pendingApprovals'] }} pending approval
                    </p>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-purple-500/10 to-purple-500/5 border-0 overflow-hidden hover:shadow-lg transition-shadow">
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
                    <span class="counter-value" data-target="{{ $metrics['winRate'] }}">0</span>%
                </h5>
                <p class="text-sm text-default-600 font-medium">Customer Win Rate</p>
                <div class="mt-3 pt-3 border-t border-default-200">
                    <p class="text-xs text-default-500">
                        <i class="size-3 me-1" data-lucide="gift"></i>
                        ${{ $metrics['totalPrizeWon'] }} in prizes
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 - Main Analytics --}}
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-5 mb-5">
        <div class="lg:col-span-2 col-span-1">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Sales & Performance Overview</h6>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2 text-xs">
                            <div class="size-3 rounded-full bg-primary"></div>
                            <span class="text-default-600">Sales</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <div class="size-3 rounded-full bg-success"></div>
                            <span class="text-default-600">Orders</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid md:grid-cols-2 grid-cols-1 mb-4 gap-4">
                        <div class="flex items-center gap-3 p-3 bg-primary/5 rounded-lg">
                            <div class="flex items-center justify-center rounded-lg size-12 bg-primary/20">
                                <i class="text-primary size-6" data-lucide="trending-up"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-default-500 text-xs">Total Sales</p>
                                <h5 class="text-lg font-bold text-default-900">${{ $metrics['totalSales'] }}</h5>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-success/5 rounded-lg">
                            <div class="flex items-center justify-center rounded-lg size-12 bg-success/20">
                                <i class="text-success size-6" data-lucide="shopping-bag"></i>
                            </div>
                            <div>
                                <p class="mb-1 text-default-500 text-xs">Total Orders</p>
                                <h5 class="text-lg font-bold text-default-900">{{ $metrics['totalOrders'] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div id="salesTrendChart" class="apex-charts"></div>
                </div>
            </div>
        </div>

        <div class="col-span-1">
            <div class="card h-full">
                <div class="card-header">
                    <h6 class="card-title">Win/Loss Distribution</h6>
                    <div class="hs-dropdown relative inline-flex">
                        <button class="hs-dropdown-toggle btn size-7 hover:bg-default-100" type="button">
                            <i class="size-4 text-default-500" data-lucide="more-vertical"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="winLossChart" class="apex-charts"></div>
                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="text-center p-3 bg-success/5 rounded-lg">
                            <h5 class="text-xl font-bold text-success">{{ $winningStats['totalWon'] }}</h5>
                            <p class="text-xs text-default-600 mt-1">Wins</p>
                        </div>
                        <div class="text-center p-3 bg-danger/5 rounded-lg">
                            <h5 class="text-xl font-bold text-danger">{{ $winningStats['totalLost'] }}</h5>
                            <p class="text-xs text-default-600 mt-1">Losses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 - Demographics & Distribution --}}
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-4 text-primary" data-lucide="smartphone"></i>
                    <h6 class="card-title">Device Distribution</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="deviceDistributionChart" class="apex-charts"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-4 text-success" data-lucide="users"></i>
                    <h6 class="card-title">Gender Distribution</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="genderChart" class="apex-charts"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-4 text-warning" data-lucide="calendar"></i>
                    <h6 class="card-title">Age Distribution</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="ageChart" class="apex-charts"></div>
            </div>
        </div>
    </div>

    {{-- Winning Statistics Banner --}}
    <div class="card mb-5 bg-gradient-to-r from-amber-50 to-yellow-50 border-amber-200">
        <div class="card-header bg-transparent border-b border-amber-200">
            <h6 class="card-title flex items-center gap-2">
                <i class="size-5 text-amber-600" data-lucide="trophy"></i>
                Winning Statistics & Highlights
            </h6>
        </div>
        <div class="card-body">
            <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-4">
                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <div class="flex items-center justify-center size-12 rounded-full bg-success/10 mx-auto mb-3">
                        <i class="size-6 text-success" data-lucide="check-circle"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-success mb-1">{{ $winningStats['totalWon'] }}</h4>
                    <p class="text-sm text-default-600">Total Wins</p>
                </div>
                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <div class="flex items-center justify-center size-12 rounded-full bg-primary/10 mx-auto mb-3">
                        <i class="size-6 text-primary" data-lucide="dollar-sign"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-primary mb-1">${{ $winningStats['totalPrizes'] }}</h4>
                    <p class="text-sm text-default-600">Total Prizes</p>
                </div>
                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <div class="flex items-center justify-center size-12 rounded-full bg-warning/10 mx-auto mb-3">
                        <i class="size-6 text-warning" data-lucide="bar-chart"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-warning mb-1">${{ $winningStats['avgPrize'] }}</h4>
                    <p class="text-sm text-default-600">Avg Prize</p>
                </div>
                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <div class="flex items-center justify-center size-12 rounded-full bg-purple-500/10 mx-auto mb-3">
                        <i class="size-6 text-purple-500" data-lucide="percent"></i>
                    </div>
                    <h4 class="text-2xl font-bold text-purple-500 mb-1">{{ $winningStats['winRate'] }}%</h4>
                    <p class="text-sm text-default-600">Win Rate</p>
                </div>
            </div>

            @if($winningStats['biggestWin'])
            <div class="mt-4 p-4 bg-gradient-to-r from-yellow-100 to-amber-100 rounded-lg border border-amber-300">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center size-16 rounded-full bg-amber-500 text-white">
                        <i class="size-8" data-lucide="crown"></i>
                    </div>
                    <div class="flex-1">
                        <h6 class="font-bold text-lg text-amber-900 mb-1">🎉 Biggest Win of the Period!</h6>
                        <p class="text-sm text-amber-800">
                            <span class="font-semibold">{{ $winningStats['biggestWin']['customer'] }}</span> won an amazing 
                            <span class="font-bold text-success text-lg">${{ $winningStats['biggestWin']['amount'] }}</span> 
                            <span class="text-xs text-amber-700">(Order: {{ $winningStats['biggestWin']['order'] }})</span>
                        </p>
                    </div>
                    <div class="text-6xl">🏆</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Top Customers Table --}}
    <div class="card mb-5">
        <div class="card-header">
            <div class="flex items-center gap-2">
                <i class="size-5 text-primary" data-lucide="award"></i>
                <h6 class="card-title">Top 10 Customers</h6>
            </div>
            <span class="text-sm text-default-600">Ranked by total spending</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-default-200">
                <thead class="bg-default-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Rank</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Customer</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Contact</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Purchases</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Total Spent</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Wins</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold text-default-700">Win Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-default-200 bg-white">
                    @forelse($topCustomers as $index => $customer)
                    <tr class="hover:bg-default-50 transition-colors">
                        <td class="px-4 py-3">
                            @if($index < 3)
                                <div class="flex items-center justify-center size-10 rounded-full font-bold text-white
                                    {{ $index === 0 ? 'bg-gradient-to-r from-yellow-400 to-yellow-600' : 
                                       ($index === 1 ? 'bg-gradient-to-r from-gray-300 to-gray-500' : 
                                        'bg-gradient-to-r from-orange-400 to-orange-600') }}">
                                    {{ $index + 1 }}
                                </div>
                            @else
                                <div class="flex items-center justify-center size-10 rounded-full bg-default-100 text-default-700 font-semibold">
                                    {{ $index + 1 }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="text-primary font-semibold">{{ substr($customer['name'], 0, 2) }}</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-default-900">{{ $customer['name'] }}</div>
                                    <div class="text-xs text-default-500">ID: {{ $customer['id'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-default-700">{{ $customer['email'] }}</div>
                            <div class="text-xs text-default-500">{{ $customer['phone'] }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 text-xs bg-primary/10 text-primary rounded-full font-medium">
                                {{ $customer['purchases'] }} orders
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-success text-lg">${{ $customer['spent'] }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 text-xs bg-success/10 text-success rounded-full font-medium">
                                {{ $customer['wins'] }} wins
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1">
                                    <div class="w-full h-2 bg-default-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-success to-emerald-600 rounded-full" 
                                             style="width: {{ $customer['win_rate'] }}%"></div>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-default-700">{{ $customer['win_rate'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="size-16 text-default-300 mb-3" data-lucide="users"></i>
                                <p class="text-default-600 font-medium">No customer data available</p>
                                <p class="text-sm text-default-500 mt-1">Data will appear here once you have customer activity</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Push Token Statistics --}}
    <div class="grid lg:grid-cols-2 grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-5 text-info" data-lucide="bell"></i>
                    <h6 class="card-title">Push Notification Stats</h6>
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="p-4 bg-info/5 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <i class="size-8 text-info" data-lucide="smartphone"></i>
                            <span class="text-2xl font-bold text-info">{{ array_sum($deviceDistribution['counts']) }}</span>
                        </div>
                        <p class="text-sm text-default-600 font-medium">Active Devices</p>
                    </div>
                    <div class="p-4 bg-success/5 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <i class="size-8 text-success" data-lucide="check-circle"></i>
                            <span class="text-2xl font-bold text-success">{{ $metrics['activeCustomers'] }}</span>
                        </div>
                        <p class="text-sm text-default-600 font-medium">With Push Tokens</p>
                    </div>
                </div>
                <div class="space-y-3">
                    @foreach($deviceDistribution['labels'] as $index => $platform)
                    <div class="flex items-center justify-between p-3 bg-default-50 rounded-lg hover:bg-default-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-white flex items-center justify-center">
                                <i class="size-5 {{ $platform == 'iOS' ? 'text-default-800' : ($platform == 'Android' ? 'text-green-500' : 'text-blue-500') }}" 
                                   data-lucide="{{ $platform == 'iOS' ? 'apple' : ($platform == 'Android' ? 'smartphone' : 'globe') }}"></i>
                            </div>
                            <span class="font-medium text-default-900">{{ $platform }}</span>
                        </div>
                        <span class="font-bold text-default-900">{{ $deviceDistribution['counts'][$index] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-5 text-warning" data-lucide="activity"></i>
                    <h6 class="card-title">Customer Activity</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="customerGrowthChart" class="apex-charts"></div>
            </div>
        </div>
    </div>

    {{-- Additional Analytics Row --}}
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-5 text-primary" data-lucide="pie-chart"></i>
                    <h6 class="card-title">Revenue by Status</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="revenueByStatusChart" class="apex-charts"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="flex items-center gap-2">
                    <i class="size-5 text-success" data-lucide="repeat"></i>
                    <h6 class="card-title">Purchase Frequency</h6>
                </div>
            </div>
            <div class="card-body">
                <div id="purchaseFrequencyChart" class="apex-charts"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-primary/5 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-primary/20 flex items-center justify-center">
                                <i class="size-5 text-primary" data-lucide="trending-up"></i>
                            </div>
                            <span class="text-sm font-medium text-default-700">Avg Order Value</span>
                        </div>
                        <span class="font-bold text-primary">${{ $metrics['avgOrderValue'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-success/5 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-success/20 flex items-center justify-center">
                                <i class="size-5 text-success" data-lucide="users"></i>
                            </div>
                            <span class="text-sm font-medium text-default-700">Active Customers</span>
                        </div>
                        <span class="font-bold text-success">{{ $metrics['activeCustomers'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-warning/5 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-warning/20 flex items-center justify-center">
                                <i class="size-5 text-warning" data-lucide="clock"></i>
                            </div>
                            <span class="text-sm font-medium text-default-700">Pending Approvals</span>
                        </div>
                        <span class="font-bold text-warning">{{ $metrics['pendingApprovals'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-purple-500/5 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                <i class="size-5 text-purple-500" data-lucide="gift"></i>
                            </div>
                            <span class="text-sm font-medium text-default-700">Total Prizes</span>
                        </div>
                        <span class="font-bold text-purple-500">${{ $metrics['totalPrizeWon'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        
        // ============================================
        // COUNTER ANIMATION - FIXED VERSION
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
            const duration = 2000; // 2 seconds
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
        // APEXCHARTS INITIALIZATION - FIXED VERSION
        // ============================================

        // Helper function to safely initialize charts
        function initChart(chartId, options) {
            const element = document.querySelector(`#${chartId}`);
            if (element) {
                try {
                    const chart = new ApexCharts(element, options);
                    chart.render();
                    return chart;
                } catch (error) {
                    console.error(`Error initializing chart ${chartId}:`, error);
                    return null;
                }
            } else {
                console.warn(`Chart element not found: #${chartId}`);
                return null;
            }
        }

        // Sales Trend Chart
        initChart('salesTrendChart', {
            chart: { 
                type: 'area', 
                height: 350, 
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            series: [
                { 
                    name: 'Sales ($)', 
                    data: @json($salesTrend['sales']),
                    type: 'area'
                },
                { 
                    name: 'Orders', 
                    data: @json($salesTrend['orders']),
                    type: 'line'
                }
            ],
            xaxis: { 
                categories: @json($salesTrend['labels']),
                labels: {
                    style: { fontSize: '12px' }
                }
            },
            stroke: { 
                curve: 'smooth', 
                width: [0, 3]
            },
            colors: ['#3b82f6', '#10b981'],
            fill: {
                type: ['gradient', 'solid'],
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.2,
                }
            },
            dataLabels: { enabled: false },
            legend: { 
                position: 'top',
                horizontalAlign: 'right',
                markers: { radius: 12 }
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 3
            },
            tooltip: {
                shared: true,
                intersect: false
            }
        });

        // Win/Loss Chart
        initChart('winLossChart', {
            chart: { 
                type: 'donut', 
                height: 280 
            },
            series: @json($winLossDistribution['counts']),
            labels: @json($winLossDistribution['labels']),
            colors: ['#10b981', '#ef4444'],
            legend: { 
                position: 'bottom',
                fontSize: '14px',
                markers: { radius: 12 }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.round(val) + "%"
                }
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
                                fontSize: '16px',
                                fontWeight: 600
                            }
                        }
                    }
                }
            }
        });

        // Customer Growth Chart
        initChart('customerGrowthChart', {
            chart: { 
                type: 'area', 
                height: 280, 
                toolbar: { show: false },
                sparkline: { enabled: false }
            },
            series: [
                { name: 'New Customers', data: @json($customerGrowth['new']) },
                { name: 'Cumulative', data: @json($customerGrowth['cumulative']) }
            ],
            xaxis: { 
                categories: @json($customerGrowth['labels']),
                labels: { style: { fontSize: '11px' } }
            },
            stroke: { 
                curve: 'smooth',
                width: 2
            },
            fill: { 
                type: 'gradient',
                gradient: {
                    opacityFrom: 0.6,
                    opacityTo: 0.1,
                }
            },
            colors: ['#6366f1', '#8b5cf6'],
            legend: { 
                position: 'top',
                fontSize: '12px',
                markers: { radius: 12 }
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 3
            }
        });

        // Revenue by Status Chart
        initChart('revenueByStatusChart', {
            chart: { 
                type: 'bar', 
                height: 280, 
                toolbar: { show: false }
            },
            series: [{ 
                name: 'Revenue', 
                data: @json($revenueByStatus['revenue']) 
            }],
            xaxis: { 
                categories: @json($revenueByStatus['labels']),
                labels: { style: { fontSize: '11px' } }
            },
            colors: ['#f59e0b', '#10b981', '#ef4444', '#3b82f6'],
            plotOptions: { 
                bar: { 
                    borderRadius: 8,
                    distributed: true,
                    columnWidth: '60%'
                } 
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 3
            }
        });

        // Device Distribution Chart
        initChart('deviceDistributionChart', {
            chart: { 
                type: 'pie', 
                height: 280 
            },
            series: @json($deviceDistribution['counts']),
            labels: @json($deviceDistribution['labels']),
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
            legend: { 
                position: 'bottom',
                fontSize: '13px',
                markers: { radius: 12 }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.round(val) + "%"
                }
            }
        });

        // Gender Chart
        initChart('genderChart', {
            chart: { 
                type: 'donut', 
                height: 280 
            },
            series: @json($genderDistribution['counts']),
            labels: @json($genderDistribution['labels']),
            colors: ['#ec4899', '#3b82f6', '#6366f1'],
            legend: { 
                position: 'bottom',
                fontSize: '13px',
                markers: { radius: 12 }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%'
                    }
                }
            }
        });

        // Age Chart
        initChart('ageChart', {
            chart: { 
                type: 'bar', 
                height: 280, 
                toolbar: { show: false }
            },
            series: [{ 
                name: 'Customers', 
                data: @json($ageDistribution['counts']) 
            }],
            xaxis: { 
                categories: @json($ageDistribution['labels']),
                labels: { style: { fontSize: '11px' } }
            },
            colors: ['#8b5cf6'],
            plotOptions: { 
                bar: { 
                    borderRadius: 6,
                    columnWidth: '70%'
                } 
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 3
            }
        });

        // Purchase Frequency Chart
        initChart('purchaseFrequencyChart', {
            chart: { 
                type: 'bar', 
                height: 280, 
                toolbar: { show: false }
            },
            series: [{ 
                name: 'Customers', 
                data: @json($purchaseFrequency['counts']) 
            }],
            xaxis: { 
                categories: @json($purchaseFrequency['labels']),
                title: { 
                    text: 'Purchase Count Range',
                    style: { fontSize: '12px', fontWeight: 500 }
                }
            },
            yaxis: {
                title: { 
                    text: 'Number of Customers',
                    style: { fontSize: '12px', fontWeight: 500 }
                }
            },
            colors: ['#06b6d4'],
            plotOptions: { 
                bar: { 
                    borderRadius: 6,
                    horizontal: true,
                    barHeight: '70%'
                } 
            },
            dataLabels: { 
                enabled: true,
                style: { fontSize: '12px' }
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 3
            }
        });

    }); // End of DOMContentLoaded
</script>
@endsection