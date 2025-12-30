@extends('layouts.vertical', ['title' => 'Customer Details'])

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/apexcharts/apexcharts.css') }}">
@endsection

@section('content')
@include('layouts.partials/page-title', ['subtitle' => 'Admin', 'title' => 'Customer Details'])

@if(session('success'))
    <div class="bg-success/10 border border-success/20 text-success px-4 py-3 rounded relative mb-4">
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('warning'))
    <div class="bg-warning/10 border border-warning/20 text-warning px-4 py-3 rounded relative mb-4">
        <span>{{ session('warning') }}</span>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h6 class="card-title">Customer Information</h6>
                @if($customer->is_blocked)
                    <span class="inline-flex px-3 py-1 bg-danger text-white rounded-full text-sm font-bold">
                        <i class="size-4 mr-1" data-lucide="shield-alert"></i> BLOCKED
                    </span>
                @else
                    <span class="inline-flex px-3 py-1 bg-success/10 text-success rounded-full text-sm font-medium">
                        <i class="size-4 mr-1" data-lucide="check-circle"></i> Active
                    </span>
                @endif
            </div>
            <div class="card-body space-y-4">
                @if($customer->is_blocked)
                    <div class="bg-danger/10 border border-danger/20 text-danger px-4 py-3 rounded">
                        <p class="font-semibold mb-1"><i class="size-4" data-lucide="alert-triangle"></i> Account Blocked</p>
                        <p class="text-sm"><b>Reason:</b> {{ $customer->block_reason ?? 'No reason provided' }}</p>
                        <p class="text-sm"><b>Blocked at:</b> {{ $customer->blocked_at?->format('M d, Y H:i') }}</p>
                        @if($customer->blockedByUser)
                            <p class="text-sm"><b>Blocked by:</b> {{ $customer->blockedByUser->name }}</p>
                        @endif
                    </div>
                @endif

                <div class="flex items-center gap-4">
                    <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="text-primary text-xl font-bold">{{ strtoupper(substr($customer->full_name ?? 'C', 0, 2)) }}</span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-lg">{{ $customer->full_name ?? 'N/A' }}</h4>
                        <p class="text-sm text-default-500">{{ $customer->phone_number }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-default-500">Email</p>
                        <p class="font-medium">{{ $customer->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-default-500">Gender</p>
                        <p class="font-medium">{{ $customer->gender == 'M' ? 'Male' : ($customer->gender == 'F' ? 'Female' : ($customer->gender ?? 'N/A')) }}</p>
                    </div>
                    <div>
                        <p class="text-default-500">Date of Birth</p>
                        <p class="font-medium">{{ $customer->dob?->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-default-500">Joined</p>
                        <p class="font-medium">{{ $customer->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    @can('customer-edit')
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn bg-primary text-white flex-1">
                            <i class="size-4 me-1" data-lucide="edit"></i>Edit
                        </a>

                        @if($customer->is_blocked)
                            <form action="{{ route('customers.unblock', $customer->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="btn bg-success text-white w-full" onclick="return confirm('Unblock this customer account?')">
                                    <i class="size-4 me-1" data-lucide="unlock"></i>Unblock
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn bg-danger text-white flex-1" data-hs-overlay="#blockModal">
                                <i class="size-4 me-1" data-lucide="shield-alert"></i>Block
                            </button>
                        @endif

                        <div class="hs-dropdown relative inline-flex flex-1">
                            <button type="button" class="hs-dropdown-toggle btn bg-info text-white w-full">
                                <i class="size-4 me-1" data-lucide="download"></i>Export
                            </button>
                            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-48">
                                <a class="flex items-center gap-x-3.5 py-2 px-3 text-sm hover:bg-default-100" href="{{ route('customers.export') }}">
                                    <i class="size-4" data-lucide="file-spreadsheet"></i> Excel Export
                                </a>
                                <a class="flex items-center gap-x-3.5 py-2 px-3 text-sm hover:bg-default-100" href="{{ route('customers.gdpr-export', $customer->id) }}">
                                    <i class="size-4" data-lucide="shield"></i> GDPR Export (HTML)
                                </a>
                                <a class="flex items-center gap-x-3.5 py-2 px-3 text-sm hover:bg-default-100" href="{{ route('customers.gdpr-export', ['customer' => $customer->id, 'format' => 'json']) }}">
                                    <i class="size-4" data-lucide="file-json"></i> GDPR Export (JSON)
                                </a>
                            </div>
                        </div>
                    @endcan
                </div>
                <a href="{{ route('customers.index') }}" class="btn bg-default-200 text-default-700 w-full">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card bg-primary/5 border border-primary/10">
                <div class="card-body text-center">
                    <p class="text-sm text-default-500 mb-1">Total Purchases</p>
                    <p class="text-2xl font-bold text-primary">{{ $analytics['total_purchases'] }}</p>
                </div>
            </div>
            <div class="card bg-success/5 border border-success/10">
                <div class="card-body text-center">
                    <p class="text-sm text-default-500 mb-1">Win Rate</p>
                    <p class="text-2xl font-bold text-success">{{ $analytics['win_rate'] }}%</p>
                </div>
            </div>
            <div class="card bg-info/5 border border-info/10">
                <div class="card-body text-center">
                    <p class="text-sm text-default-500 mb-1">Total Spent</p>
                    <p class="text-2xl font-bold text-info">฿{{ $analytics['total_spent'] }}</p>
                </div>
            </div>
            <div class="card bg-warning/5 border border-warning/10">
                <div class="card-body text-center">
                    <p class="text-sm text-default-500 mb-1">Prize Won</p>
                    <p class="text-2xl font-bold text-warning">฿{{ $analytics['total_prize_won'] }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="card bg-success/10 border border-success/20">
                <div class="card-body text-center py-3">
                    <p class="text-xs text-default-500">Wins</p>
                    <p class="text-xl font-bold text-success">{{ $analytics['win_count'] }}</p>
                </div>
            </div>
            <div class="card bg-danger/10 border border-danger/20">
                <div class="card-body text-center py-3">
                    <p class="text-xs text-default-500">Not Won</p>
                    <p class="text-xl font-bold text-danger">{{ $analytics['not_won_count'] }}</p>
                </div>
            </div>
            <div class="card bg-warning/10 border border-warning/20">
                <div class="card-body text-center py-3">
                    <p class="text-xs text-default-500">Pending</p>
                    <p class="text-xl font-bold text-warning">{{ $analytics['pending_count'] }}</p>
                </div>
            </div>
            <div class="card bg-info/10 border border-info/20">
                <div class="card-body text-center py-3">
                    <p class="text-xs text-default-500">Avg Prize</p>
                    <p class="text-xl font-bold text-info">฿{{ $analytics['avg_prize_per_win'] }}</p>
                </div>
            </div>
            <div class="card bg-default-100 border border-default-200">
                <div class="card-body text-center py-3">
                    <p class="text-xs text-default-500">Purchases/Day</p>
                    <p class="text-xl font-bold text-default-700">{{ $analytics['purchase_frequency'] }}</p>
                </div>
            </div>
        </div>

        @if($analytics['biggest_win'])
        <div class="card bg-gradient-to-r from-success/20 to-success/5 border border-success/20">
            <div class="card-body flex items-center gap-4">
                <div class="size-12 rounded-full bg-success flex items-center justify-center">
                    <i class="size-6 text-white" data-lucide="trophy"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-default-500">Biggest Win</p>
                    <p class="text-xl font-bold text-success">฿{{ number_format($analytics['biggest_win']->prize_won, 2) }}</p>
                    <p class="text-xs text-default-500">{{ $analytics['biggest_win']->order_number }} - {{ $analytics['biggest_win']->lotteryTicket->ticket_number ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Monthly Performance</h6>
                </div>
                <div class="card-body">
                    <div id="monthlyChart" class="apex-charts"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">Win/Loss Distribution</h6>
                </div>
                <div class="card-body">
                    <div id="winLossChart" class="apex-charts"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Recent Purchases</h6>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-default-200">
                    <thead class="bg-default-150">
                        <tr class="text-sm font-normal text-default-700 whitespace-nowrap">
                            <th class="px-3.5 py-3 text-start">Order #</th>
                            <th class="px-3.5 py-3 text-start">Ticket</th>
                            <th class="px-3.5 py-3 text-start">Qty</th>
                            <th class="px-3.5 py-3 text-start">Amount</th>
                            <th class="px-3.5 py-3 text-start">Status</th>
                            <th class="px-3.5 py-3 text-start">Prize</th>
                            <th class="px-3.5 py-3 text-start">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPurchases as $purchase)
                        <tr class="text-default-800 font-normal text-sm whitespace-nowrap">
                            <td class="px-3.5 py-3 font-mono">{{ $purchase->order_number }}</td>
                            <td class="px-3.5 py-3 font-mono">{{ $purchase->lotteryTicket->ticket_number ?? 'N/A' }}</td>
                            <td class="px-3.5 py-3">{{ $purchase->quantity }}</td>
                            <td class="px-3.5 py-3">฿{{ number_format($purchase->total_price, 2) }}</td>
                            <td class="px-3.5 py-3">
                                @switch($purchase->status)
                                    @case('won')
                                        <span class="px-2 py-1 text-xs rounded-full bg-success/10 text-success">Won</span>
                                        @break
                                    @case('not_won')
                                        <span class="px-2 py-1 text-xs rounded-full bg-danger/10 text-danger">Not Won</span>
                                        @break
                                    @case('pending')
                                        <span class="px-2 py-1 text-xs rounded-full bg-warning/10 text-warning">Pending</span>
                                        @break
                                    @case('approved')
                                        <span class="px-2 py-1 text-xs rounded-full bg-info/10 text-info">Approved</span>
                                        @break
                                    @case('rejected')
                                        <span class="px-2 py-1 text-xs rounded-full bg-danger text-white">Rejected</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-3.5 py-3 font-medium text-success">
                                @if($purchase->prize_won)
                                    ฿{{ number_format($purchase->prize_won, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-3.5 py-3">{{ $purchase->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-3.5 py-8 text-center text-default-500">
                                No purchases found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(!$customer->is_blocked)
<div id="blockModal" class="hs-overlay hidden size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none">
    <div class="hs-overlay-animation-target hs-overlay-open:scale-100 hs-overlay-open:opacity-100 scale-95 opacity-0 ease-in-out transition-all duration-200 sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center">
        <div class="card w-full flex flex-col border border-default-200 shadow-2xs rounded-xl pointer-events-auto">
            <div class="card-header">
                <h3 class="font-semibold text-base text-default-800 flex items-center gap-2">
                    <i class="size-5 text-danger" data-lucide="shield-alert"></i>
                    <span>Block Customer Account</span>
                </h3>
                <button type="button" class="size-5 text-default-800" data-hs-overlay="#blockModal">
                    <i data-lucide="x" class="size-5"></i>
                </button>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.block', $customer->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Reason for Blocking</label>
                        <textarea name="block_reason" class="form-input" rows="4" required placeholder="Why is this account being blocked?"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn bg-danger text-white">Confirm Block</button>
                        <button type="button" class="btn bg-default-200" data-hs-overlay="#blockModal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('script')
<script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ApexCharts !== 'undefined') {
        const winCount = {{ $analytics['win_count'] }};
        const notWonCount = {{ $analytics['not_won_count'] }};

        const winLossChart = new ApexCharts(document.querySelector("#winLossChart"), {
            series: [winCount, notWonCount],
            labels: ['Wins', 'Not Won'],
            chart: {
                type: 'donut',
                height: 280
            },
            colors: ['#22c55e', '#ef4444'],
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return Math.round(val) + '%'
                }
            },
            legend: {
                position: 'bottom'
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
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                }
                            }
                        }
                    }
                }
            }
        });
        winLossChart.render();

        const monthlyChart = new ApexCharts(document.querySelector("#monthlyChart"), {
            series: [{
                name: 'Wins',
                data: @json($monthlyWins)
            }],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false }
            },
            colors: ['#22c55e'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '60%'
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($monthlyLabels),
                labels: {
                    rotate: -45,
                    style: { fontSize: '11px' }
                }
            },
            yaxis: {
                title: { text: 'Wins' }
            }
        });
        monthlyChart.render();
    }
});
</script>
@endsection
