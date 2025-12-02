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
                                            // Handle both array (from model cast) and string (legacy data) formats
                                            $prizesRaw = $data->prizes;
                                            if (is_string($prizesRaw)) {
                                                $prizes = json_decode($prizesRaw, true) ?? [];
                                            } else {
                                                $prizes = is_array($prizesRaw) ? $prizesRaw : [];
                                            }
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

    <!-- Enhanced Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 z-[9999] hidden" style="backdrop-filter: blur(4px);">
        <!-- Overlay with proper opacity -->
        <div class="absolute inset-0 bg-black/70 transition-opacity duration-300"></div>
        
        <!-- Content Container -->
        <div class="relative w-full h-full flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-100 modal-content">
                <!-- Decorative top border -->
                <div class="h-2 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-t-2xl"></div>
                
                <div class="p-8">
                    <!-- Animated Spinner -->
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <!-- Outer rotating circle -->
                            <svg class="animate-spin h-20 w-20 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <!-- Inner pulsing circle -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="h-12 w-12 bg-primary/20 rounded-full animate-pulse"></div>
                            </div>
                            <!-- Center dot -->
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="h-3 w-3 bg-primary rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Text Content -->
                    <div class="text-center space-y-3">
                        <h3 id="loadingText" class="text-2xl font-bold text-gray-900">
                            Syncing data...
                        </h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Please wait, this may take a moment.<br>
                            <span class="text-xs text-gray-500">Do not close this window.</span>
                        </p>
                    </div>
                    
                    <!-- Enhanced Progress Bar -->
                    <div class="mt-6">
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                            <div class="progress-bar h-full rounded-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 transition-all duration-300 ease-out animate-progress" style="width: 0%"></div>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-gray-500">Processing...</span>
                            <span id="progressPercent" class="text-xs font-semibold text-primary">0%</span>
                        </div>
                    </div>
                    
                    <!-- Loading dots animation -->
                    <div class="flex justify-center items-center gap-2 mt-6">
                        <div class="h-2 w-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="h-2 w-2 bg-primary rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="h-2 w-2 bg-primary rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Quick View Modal -->
    <div id="quickViewModal" class="fixed inset-0 z-[9998] hidden" style="backdrop-filter: blur(4px);" onclick="if(event.target === this) hideQuickView()">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/60 transition-opacity duration-300"></div>
        
        <!-- Content -->
        <div class="relative w-full h-full flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-100 modal-content">
                <!-- Header with gradient -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-2xl flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                <i class="size-6" data-lucide="award"></i>
                            </div>
                            <span id="quickViewTitle">Draw Results</span>
                        </h3>
                        <button onclick="hideQuickView()" class="p-2 hover:bg-white/20 rounded-lg transition-colors duration-200">
                            <i class="size-6" data-lucide="x"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Scrollable Content -->
                <div class="overflow-y-auto max-h-[calc(90vh-180px)] px-8 py-6">
                    <div id="quickViewContent"></div>
                </div>
                
                <!-- Footer -->
                <div class="border-t border-gray-200 px-8 py-4 bg-gray-50">
                    <div class="flex justify-end gap-3">
                        <button onclick="hideQuickView()" class="btn btn-sm bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 px-6">
                            Close
                        </button>
                        <a id="viewFullDetails" href="#" class="btn btn-sm bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg px-6">
                            <i class="size-4 me-2" data-lucide="external-link"></i>
                            View Full Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden;
        }
        
        /* Modal animations */
        #loadingModal:not(.hidden) .modal-content,
        #quickViewModal:not(.hidden) .modal-content {
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        /* Progress bar animation */
        @keyframes progress {
            0% {
                width: 0%;
            }
            50% {
                width: 75%;
            }
            100% {
                width: 95%;
            }
        }
        
        .animate-progress {
            animation: progress 3s ease-in-out infinite;
        }
        
        /* Smooth transitions for hiding modals */
        .modal-hiding {
            animation: modalSlideOut 0.2s ease-in;
        }
        
        @keyframes modalSlideOut {
            from {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
            to {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
        }
        
        /* Custom scrollbar for modal content */
        #quickViewContent::-webkit-scrollbar {
            width: 8px;
        }
        
        #quickViewContent::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        #quickViewContent::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        #quickViewContent::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
    
    <script>
        let progressInterval;
        
        function showLoading(text = 'Syncing data...') {
            const modal = document.getElementById('loadingModal');
            const loadingText = document.getElementById('loadingText');
            const progressBar = modal.querySelector('.progress-bar');
            const progressPercent = document.getElementById('progressPercent');
            
            loadingText.textContent = text;
            modal.classList.remove('hidden');
            document.body.classList.add('modal-open');
            
            // Animate progress bar
            let progress = 0;
            clearInterval(progressInterval);
            progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 95) progress = 95;
                progressBar.style.width = progress + '%';
                progressPercent.textContent = Math.round(progress) + '%';
            }, 500);
        }

        function hideLoading() {
            const modal = document.getElementById('loadingModal');
            const modalContent = modal.querySelector('.modal-content');
            const progressBar = modal.querySelector('.progress-bar');
            
            clearInterval(progressInterval);
            
            // Complete progress bar
            progressBar.style.width = '100%';
            document.getElementById('progressPercent').textContent = '100%';
            
            // Smooth hide animation
            modalContent.classList.add('modal-hiding');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.classList.remove('modal-open');
                modalContent.classList.remove('modal-hiding');
                progressBar.style.width = '0%';
                document.getElementById('progressPercent').textContent = '0%';
            }, 200);
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
                        html += '<div class="space-y-4">';
                        data.prizes.forEach((prize, idx) => {
                            const numbers = Array.isArray(prize.number) ? prize.number : [prize.number];
                            const reward = parseInt(prize.reward).toLocaleString();
                            
                            let cardClass = 'border-2 border-default-200 hover:border-default-300';
                            let badgeClass = 'inline-flex py-1.5 px-3 rounded-lg text-base font-bold font-mono shadow-sm';
                            let iconHtml = '';
                            
                            if (prize.name === 'First Prize') {
                                cardClass = 'border-2 border-yellow-400 bg-gradient-to-r from-yellow-50 to-amber-50 hover:shadow-lg';
                                badgeClass += ' bg-gradient-to-r from-yellow-200 to-amber-200 text-yellow-900 shadow-md';
                                iconHtml = '<i class="size-5 me-1.5 text-yellow-600" data-lucide="trophy"></i>';
                            } else if (prize.name === '1st Prize Neighbor') {
                                cardClass = 'border-2 border-blue-300 bg-gradient-to-r from-blue-50 to-cyan-50 hover:shadow-lg';
                                badgeClass += ' bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-900';
                                iconHtml = '<i class="size-4 me-1.5 text-blue-600" data-lucide="star"></i>';
                            } else if (prize.name === 'Second Prize') {
                                cardClass = 'border-2 border-gray-300 bg-gradient-to-r from-gray-50 to-slate-50 hover:shadow-lg';
                                badgeClass += ' bg-gradient-to-r from-gray-100 to-slate-100 text-gray-900';
                                iconHtml = '<i class="size-4 me-1.5 text-gray-600" data-lucide="award"></i>';
                            } else if (prize.name === 'Third Prize') {
                                cardClass = 'border-2 border-orange-300 bg-gradient-to-r from-orange-50 to-amber-50 hover:shadow-lg';
                                badgeClass += ' bg-gradient-to-r from-orange-100 to-amber-100 text-orange-900';
                                iconHtml = '<i class="size-4 me-1.5 text-orange-600" data-lucide="medal"></i>';
                            } else {
                                badgeClass += ' bg-gradient-to-r from-purple-100 to-pink-100 border border-purple-200 text-purple-900';
                            }
                            
                            html += `
                                <div class="border ${cardClass} rounded-xl p-5 transition-all duration-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="font-semibold text-lg text-default-900 flex items-center gap-2">
                                            ${iconHtml}${prize.name}
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-lg text-sm bg-green-100 border-2 border-green-300 text-green-700 font-semibold shadow-sm">
                                            <i class="size-4" data-lucide="banknote"></i>฿${reward}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        ${numbers.map(num => `<span class="${badgeClass}">${num}</span>`).join('')}
                                    </div>
                                    ${numbers.length > 5 ? `<div class="mt-3 pt-3 border-t border-default-200 text-xs text-default-500 font-medium">Total: ${numbers.length} winning numbers</div>` : ''}
                                </div>
                            `;
                        });
                        html += '</div>';
                    }

                    if (data.running_numbers && data.running_numbers.length) {
                        html += '<div class="mt-8"><h4 class="font-bold text-xl mb-4 text-default-900 flex items-center gap-2 pb-2 border-b-2 border-indigo-200"><i class="size-6 text-indigo-600" data-lucide="list"></i>Running Numbers</h4><div class="space-y-3">';
                        data.running_numbers.forEach(rn => {
                            const numbers = Array.isArray(rn.number) ? rn.number : [rn.number];
                            const reward = parseInt(rn.reward).toLocaleString();
                            html += `
                                <div class="border-2 border-indigo-200 rounded-xl p-4 bg-gradient-to-r from-indigo-50 to-blue-50 hover:shadow-lg transition-all duration-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-semibold text-default-800">${rn.name}</span>
                                        <span class="text-sm text-green-600 font-bold bg-green-100 px-3 py-1 rounded-lg border border-green-200">฿${reward}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        ${numbers.map(num => `<span class="inline-flex py-1 px-2.5 rounded-lg text-sm font-mono font-bold bg-white border-2 border-indigo-200 text-indigo-900 shadow-sm">${num}</span>`).join('')}
                                    </div>
                                    ${numbers.length > 10 ? `<div class="mt-3 pt-3 border-t border-indigo-100 text-xs text-default-500 font-medium">Total: ${numbers.length} numbers</div>` : ''}
                                </div>
                            `;
                        });
                        html += '</div></div>';
                    }

                    document.getElementById('quickViewContent').innerHTML = html;
                    document.getElementById('quickViewModal').classList.remove('hidden');
                    document.body.classList.add('modal-open');
                    
                    // Re-initialize lucide icons
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                })
                .catch(err => {
                    hideLoading();
                    console.error('Error loading quick view:', err);
                    alert('❌ Failed to load draw result details. Please try again.');
                });
        }

        function hideQuickView() {
            const modal = document.getElementById('quickViewModal');
            const modalContent = modal.querySelector('.modal-content');
            
            modalContent.classList.add('modal-hiding');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.classList.remove('modal-open');
                modalContent.classList.remove('modal-hiding');
            }, 200);
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const quickViewModal = document.getElementById('quickViewModal');
                const loadingModal = document.getElementById('loadingModal');
                
                if (!quickViewModal.classList.contains('hidden')) {
                    hideQuickView();
                }
            }
        });

        // Prevent scroll when modal is open
        window.addEventListener('DOMContentLoaded', function() {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        const hasModalOpen = document.body.classList.contains('modal-open');
                        if (hasModalOpen) {
                            document.body.style.overflow = 'hidden';
                            document.body.style.paddingRight = '0px'; // Prevent layout shift
                        } else {
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }
                    }
                });
            });
            
            observer.observe(document.body, {
                attributes: true
            });
        });
    </script>
@endsection