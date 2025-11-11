@extends('layouts.vertical', ['title' => 'Lottery Draw Results'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Lottery', 'title' => 'Draw Results'])

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 mb-5">
        <div class="card">
            <div class="card-header flex items-center gap-2 flex-wrap">
                <form method="GET" action="{{ route('draw_results.index') }}" class="flex gap-2 flex-wrap items-center">
                    <!-- <div class="relative" style="width: 10rem;">
                        <input 
                            name="search"
                            value="{{ request('search') }}"
                            class="ps-10 form-input form-input-sm w-full"
                            placeholder="Search..."
                            type="text"
                        />
                        <div class="absolute inset-y-0 left-0 flex items-center ps-3">
                            <i class="size-4 text-default-500" data-lucide="search"></i>
                        </div>
                    </div> -->
                    
                    <div style="width: 8rem;">
                        <select name="year" class="form-input form-input-sm w-full">
                            <option value="">All Years</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div style="width: 8rem;">
                        <select name="month" class="form-input form-input-sm w-full">
                            <option value="">All Months</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div style="width: 8rem;">
                        <select name="sort_by" class="form-input form-input-sm w-full">
                            <option value="draw_date" {{ request('sort_by', 'draw_date') == 'draw_date' ? 'selected' : '' }}>Date</option>
                            <option value="date_en" {{ request('sort_by') == 'date_en' ? 'selected' : '' }}>English Date</option>
                        </select>
                    </div>

                    <div style="width: 7rem;">
                        <select name="sort_dir" class="form-input form-input-sm w-full">
                            <option value="desc" {{ request('sort_dir', 'desc') == 'desc' ? 'selected' : '' }}>Newest</option>
                            <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Oldest</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-xs bg-primary text-white">
                        <i class="size-4 me-1" data-lucide="filter"></i>Filter
                    </button>
                    <a href="{{ route('draw_results.index') }}" class="btn btn-xs bg-default-200 text-default-600 hover:bg-default-300">Clear</a>
                </form>
                <button onclick="syncLatest()" class="btn btn-xs bg-success text-white sync-btn">
                            <i class="size-4 me-1" data-lucide="refresh-cw"></i>Sync Latest
                        </button>
                        <button onclick="syncAll()" class="btn btn-xs bg-warning text-dark sync-btn">
                            <i class="size-4 me-1" data-lucide="download"></i>Sync All
                        </button>
            </div>

            <div class="flex flex-col">
                <div class="overflow-x-auto">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-default-200">
                                <thead class="bg-default-150">
                                    <tr class="text-sm font-normal text-default-700">
                                        <th class="px-3.5 py-3 text-start">Draw Date</th>
                                        <th class="px-3.5 py-3 text-start">Thai Date</th>
                                        <th class="px-3.5 py-3 text-start">First Prize</th>
                                        <th class="px-3.5 py-3 text-start">Total Prizes</th>
                                        <th class="px-3.5 py-3 text-start">Status</th>
                                        <th class="px-3.5 py-3 text-start">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-default-200">
                                    @forelse ($results as $data)
                                        @php
                                            $prizes = json_decode($data->prizes, true) ?: [];
                                            $firstPrize = collect($prizes)->firstWhere('name', 'รางวัลที่ 1');
                                            $firstNumber = $firstPrize ? ($firstPrize['number'][0] ?? '-') : '-';
                                            $totalPrizeCount = collect($prizes)->sum(function($p) {
                                                return count(is_array($p['number']) ? $p['number'] : [$p['number']]);
                                            });
                                        @endphp
                                        <tr class="text-default-800 font-normal text-sm hover:bg-default-100 transition">
                                            <td class="px-3.5 py-2.5">
                                                <div class="font-medium text-default-900">{{ $data->date_en }}</div>
                                                <div class="text-xs text-default-500">
                                                    <i class="size-3 inline" data-lucide="calendar"></i>
                                                    {{ \Carbon\Carbon::parse($data->draw_date)->format('d M Y') }}
                                                </div>
                                            </td>
                                            <td class="px-3.5 py-2.5 whitespace-nowrap">
                                                <span class="text-sm text-default-700">{{ $data->date_th }}</span>
                                            </td>
                                            <td class="px-3.5 py-2.5">
                                                <span class="inline-flex py-1.5 px-4 rounded-lg text-lg font-bold bg-gradient-to-r from-yellow-100 to-amber-100 border-2 border-yellow-400 text-yellow-900 font-mono shadow-sm">
                                                    <i class="size-5 me-1.5 text-yellow-600" data-lucide="trophy"></i>
                                                    {{ $firstNumber }}
                                                </span>
                                            </td>
                                            <td class="px-3.5 py-2.5 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-blue-100 border border-blue-200 text-blue-600">
                                                    <i class="size-3" data-lucide="gift"></i>
                                                    {{ count($prizes) }} categories
                                                </span>
                                                <div class="text-xs text-default-500 mt-1">{{ $totalPrizeCount }} numbers</div>
                                            </td>
                                            <td class="px-3.5 py-2.5 whitespace-nowrap">
                                                @if(\Carbon\Carbon::parse($data->draw_date)->isToday())
                                                    <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-green-100 border border-green-200 text-green-600">
                                                        <i class="size-3" data-lucide="zap"></i> Latest
                                                    </span>
                                                @elseif(\Carbon\Carbon::parse($data->draw_date)->isCurrentMonth())
                                                    <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-purple-100 border border-purple-200 text-purple-600">
                                                        <i class="size-3" data-lucide="clock"></i> Recent
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded text-xs font-normal bg-default-100 border border-default-200 text-default-500">
                                                        <i class="size-3" data-lucide="archive"></i> Archived
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3.5 py-2.5 whitespace-nowrap">
                                                <div class="flex gap-2">
                                                    <a href="{{ route('draw_results.show', $data->id) }}" class="text-blue-500 hover:text-blue-600" title="View Details">
                                                        <i class="size-4" data-lucide="eye"></i>
                                                    </a>
                                                    <button onclick="showQuickView({{ $data->id }})" class="text-purple-500 hover:text-purple-600" title="Quick Preview">
                                                        <i class="size-4" data-lucide="zap"></i>
                                                    </button>
                                                    @if($data->endpoint)
                                                        <a href="{{ $data->endpoint }}" target="_blank" class="text-green-500 hover:text-green-600" title="API Source">
                                                            <i class="size-4" data-lucide="link"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-default-400 py-8">
                                                <div class="flex flex-col items-center gap-2">
                                                    <i class="size-12 opacity-50" data-lucide="inbox"></i>
                                                    <p class="text-lg font-medium">No draw results found.</p>
                                                    <p class="text-sm">Try syncing data or adjusting filters.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                @if($results->count())
                    <div class="card-footer">
                        <p class="text-default-500 text-sm">Showing <b>{{ $results->firstItem() }}</b> to <b>{{ $results->lastItem() }}</b> of <b>{{ $results->total() }}</b> Results</p>
                        <nav aria-label="Pagination" class="flex items-center gap-2">
                            {{ $results->withQueryString()->links() }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg px-8 py-6 flex flex-col items-center gap-3 shadow-xl max-w-md">
            <div class="relative">
                <svg class="animate-spin h-12 w-12 text-primary" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="text-center">
                <span id="loadingText" class="text-lg font-semibold text-default-900">Syncing data...</span>
                <p class="text-sm text-default-500 mt-1">Please wait, this may take a moment</p>
            </div>
            <div class="w-full bg-default-200 rounded-full h-2 overflow-hidden">
                <div class="bg-primary h-full rounded-full animate-pulse" style="width: 70%"></div>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden p-4" onclick="if(event.target === this) hideQuickView()">
        <div class="bg-white rounded-lg px-8 py-6 max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-2xl text-default-900 flex items-center gap-2">
                    <i class="size-6 text-primary" data-lucide="award"></i>
                    <span id="quickViewTitle"></span>
                </h3>
                <button onclick="hideQuickView()" class="text-default-400 hover:text-default-600 transition">
                    <i class="size-6" data-lucide="x"></i>
                </button>
            </div>
            <div id="quickViewContent"></div>
            <div class="mt-6 flex justify-end gap-2">
                <button onclick="hideQuickView()" class="btn btn-sm bg-default-200 text-default-700 hover:bg-default-300">
                    Close
                </button>
                <a id="viewFullDetails" href="#" class="btn btn-sm bg-primary text-white hover:bg-primary-600">
                    <i class="size-4 me-1" data-lucide="external-link"></i>
                    View Full Details
                </a>
            </div>
        </div>
    </div>


    
    <script>
        function showLoading(text = 'Syncing data...') {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingModal').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loadingModal').classList.add('hidden');
        }

        function syncLatest() {
            showLoading('Syncing latest draw result...');
            window.location.href = '{{ route("draw_results.syncLatest") }}';
        }

        function syncAll() {
            if (!confirm('⚠️ This will sync all historical data and may take several minutes. Continue?')) {
                return;
            }
            showLoading('Syncing all draw results... This may take a few minutes.');
            window.location.href = '{{ route("draw_results.syncAll") }}';
        }

        function showQuickView(id) {
            showLoading('Loading draw details...');
            
            fetch(`/draw_results/${id}`)
                .then(r => r.json())
                .then(data => {
                    hideLoading();
                    document.getElementById('quickViewTitle').textContent = data.date_en;
                    document.getElementById('viewFullDetails').href = `/draw_results/${id}/detail`;
                    
                    let html = '';

                    if (data.prizes && data.prizes.length) {
                        html += '<div class="space-y-3">';
                        data.prizes.forEach((prize, idx) => {
                            const numbers = Array.isArray(prize.number) ? prize.number : [prize.number];
                            const reward = parseInt(prize.reward).toLocaleString();
                            
                            let cardClass = 'border-2 border-default-200';
                            let badgeClass = 'inline-flex py-1.5 px-3 rounded-lg text-base font-bold font-mono';
                            let iconHtml = '';
                            
                            if (prize.name === 'First Prize') {
                                cardClass = 'border-2 border-yellow-400 bg-gradient-to-r from-yellow-50 to-amber-50';
                                badgeClass += ' bg-gradient-to-r from-yellow-200 to-amber-200 text-yellow-900 shadow-md';
                                iconHtml = '<i class="size-5 me-1.5 text-yellow-600" data-lucide="trophy"></i>';
                            } else if (prize.name === '1st Prize Neighbor') {
                                cardClass = 'border-2 border-blue-300 bg-gradient-to-r from-blue-50 to-cyan-50';
                                badgeClass += ' bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-900';
                                iconHtml = '<i class="size-4 me-1.5 text-blue-600" data-lucide="star"></i>';
                            } else if (prize.name === 'Second Prize') {
                                cardClass = 'border-2 border-gray-300 bg-gradient-to-r from-gray-50 to-slate-50';
                                badgeClass += ' bg-gradient-to-r from-gray-100 to-slate-100 text-gray-900';
                                iconHtml = '<i class="size-4 me-1.5 text-gray-600" data-lucide="award"></i>';
                            } else if (prize.name === 'Third Prize') {
                                cardClass = 'border-2 border-orange-300 bg-gradient-to-r from-orange-50 to-amber-50';
                                badgeClass += ' bg-gradient-to-r from-orange-100 to-amber-100 text-orange-900';
                                iconHtml = '<i class="size-4 me-1.5 text-orange-600" data-lucide="medal"></i>';
                            } else {
                                badgeClass += ' bg-gradient-to-r from-purple-100 to-pink-100 border border-purple-200 text-purple-900';
                            }
                            
                            html += `
                                <div class="border ${cardClass} rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-semibold text-default-900 flex items-center gap-1">
                                            ${iconHtml}${prize.name}
                                        </span>
                                        <span class="inline-flex items-center gap-1 py-0.5 px-2 rounded text-xs bg-green-100 border border-green-200 text-green-700 font-medium">
                                            <i class="size-3" data-lucide="banknote"></i>฿${reward}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        ${numbers.map(num => `<span class="${badgeClass}">${num}</span>`).join('')}
                                    </div>
                                    ${numbers.length > 5 ? `<div class="mt-2 text-xs text-default-500">Total: ${numbers.length} numbers</div>` : ''}
                                </div>
                            `;
                        });
                        html += '</div>';
                    }

                    if (data.running_numbers && data.running_numbers.length) {
                        html += '<div class="mt-6"><h4 class="font-semibold text-lg mb-3 text-default-900 flex items-center gap-2"><i class="size-5 text-indigo-600" data-lucide="list"></i>Running Numbers</h4><div class="space-y-2">';
                        data.running_numbers.forEach(rn => {
                            const numbers = Array.isArray(rn.number) ? rn.number : [rn.number];
                            const reward = parseInt(rn.reward).toLocaleString();
                            html += `
                                <div class="border-2 border-indigo-200 rounded-lg p-3 bg-gradient-to-r from-indigo-50 to-blue-50">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-default-700">${rn.name}</span>
                                        <span class="text-xs text-green-600 font-medium">฿${reward}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        ${numbers.map(num => `<span class="inline-flex py-1 px-2 rounded text-sm font-mono font-semibold bg-white border border-indigo-200 text-indigo-900">${num}</span>`).join('')}
                                    </div>
                                    ${numbers.length > 10 ? `<div class="mt-1 text-xs text-default-500">Total: ${numbers.length} numbers</div>` : ''}
                                </div>
                            `;
                        });
                        html += '</div></div>';
                    }

                    document.getElementById('quickViewContent').innerHTML = html;
                    document.getElementById('quickViewModal').classList.remove('hidden');
                    
                    // Re-initialize lucide icons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                })
                .catch(err => {
                    hideLoading();
                    console.error('Error loading quick view:', err);
                    alert('Failed to load draw result details. Please try again.');
                });
        }

        function hideQuickView() {
            document.getElementById('quickViewModal').classList.add('hidden');
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideQuickView();
            }
        });
        </script>
@endsection