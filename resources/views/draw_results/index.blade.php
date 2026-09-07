@extends('layouts.vertical', ['title' => 'Lottery Draw Results'])

@section('content')
    @include('layouts.partials/page-title', ['subtitle' => 'Lottery', 'title' => 'Draw Results'])

    {{-- ---------- Toolbar ---------- --}}
    <div class="card mb-5">
        <div class="card-body py-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-ui.filter action="{{ route('draw_results.index') }}" class="flex flex-wrap items-end gap-2">
                    <div>
                        <label for="f-year" class="block text-xs font-medium text-default-500 mb-1">Year</label>
                        <x-ui.select id="f-year" name="year" class="form-input form-input-sm w-32">
                            <option value="">All years</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div>
                        <label for="f-month" class="block text-xs font-medium text-default-500 mb-1">Month</label>
                        <x-ui.select id="f-month" name="month" class="form-input form-input-sm w-36">
                            <option value="">All months</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected(request('month') == $m)>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </x-ui.select>
                    </div>

                    <div>
                        <label for="f-sort" class="block text-xs font-medium text-default-500 mb-1">Order</label>
                        <x-ui.select id="f-sort" name="sort_dir" class="form-input form-input-sm w-32">
                            <option value="desc" @selected(request('sort_dir', 'desc') === 'desc')>Newest first</option>
                            <option value="asc" @selected(request('sort_dir') === 'asc')>Oldest first</option>
                        </x-ui.select>
                    </div>

                    <button type="submit" class="btn btn-sm bg-primary text-white hover:bg-primary/90">
                        <i class="size-4 me-1.5" data-lucide="filter"></i>Apply
                    </button>

                    @if (request()->hasAny(['year', 'month', 'sort_dir']))
                        <a href="{{ route('draw_results.index') }}"
                            class="btn btn-sm bg-default-100 text-default-600 hover:bg-default-200">Clear</a>
                    @endif
                </x-ui.filter>

                @can('lottery-edit')
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($historyImported)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-md border border-green-500/25 bg-green-500/10 px-2.5 py-1 text-xs font-medium text-green-600 dark:text-green-400">
                                <i class="size-3.5" data-lucide="check"></i> History imported
                            </span>
                        @else
                            <form method="POST" action="{{ route('draw_results.syncAll') }}" data-sync
                                data-sync-message="Importing historical draw results…">
                                @csrf
                                <button type="submit"
                                    class="btn btn-sm bg-default-100 text-default-700 hover:bg-default-200">
                                    <i class="size-4 me-1.5" data-lucide="download"></i>Import history
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('draw_results.syncLatest') }}" data-sync
                            data-sync-message="Syncing the latest draw result…">
                            @csrf
                            <button type="submit" class="btn btn-sm bg-primary text-white hover:bg-primary/90">
                                <i class="size-4 me-1.5" data-lucide="refresh-cw"></i>Sync latest
                            </button>
                        </form>
                    </div>
                @endcan
            </div>

            <p class="mt-3 text-xs text-default-500">
                Results source:
                <a href="https://xn--t3cmiit.com" target="_blank" rel="noopener noreferrer"
                    class="underline hover:text-default-700">ผลหวย.com</a>
                · Government Lottery Office
            </p>
        </div>
    </div>

    {{-- ---------- Results table ---------- --}}
    <div class="card">
        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <x-ui.table class="min-w-full divide-y divide-default-200" caption="Lottery draw results">
                    <thead class="bg-default-100">
                        <tr class="text-xs font-semibold uppercase tracking-wide text-default-500">
                            <th class="px-4 py-3 text-start">Draw date</th>
                            <th class="px-4 py-3 text-start">First prize</th>
                            <th class="px-4 py-3 text-start">Coverage</th>
                            <th class="px-4 py-3 text-start">Status</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-default-200">
                        @forelse ($results as $data)
                            @php
                                $prizes = \App\Support\PrizeLabels::normalise($data->prizes);
                                $running = \App\Support\PrizeLabels::normalise($data->running_numbers);
                                $first = collect($prizes)->firstWhere('name', 'First Prize');
                                $firstNumber = $first['number'][0] ?? null;
                                $numberCount =
                                    \App\Support\PrizeLabels::countNumbers($prizes) +
                                    \App\Support\PrizeLabels::countNumbers($running);
                                $drawDate = \Carbon\Carbon::parse($data->draw_date);
                            @endphp
                            <tr class="text-sm text-default-700 hover:bg-default-100/60 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-default-900">
                                        {{ $data->date_en ?? $drawDate->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-default-500 mt-0.5">{{ $data->date_th ?? $drawDate->format('Y-m-d') }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    @if ($firstNumber)
                                        <span
                                            class="inline-flex items-center gap-2 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 font-mono text-base font-bold tabular-nums tracking-widest text-amber-700 dark:text-amber-400">
                                            <i class="size-4 shrink-0" data-lucide="trophy"></i>{{ $firstNumber }}
                                        </span>
                                    @else
                                        <span class="text-default-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-default-800">{{ count($prizes) }} prize tiers</div>
                                    <div class="text-xs text-default-500 mt-0.5">{{ number_format($numberCount) }} numbers</div>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($drawDate->isToday())
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-md border border-green-500/25 bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-600 dark:text-green-400">
                                            <i class="size-3" data-lucide="zap"></i> Latest
                                        </span>
                                    @elseif ($drawDate->isCurrentMonth())
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-md border border-primary/25 bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                                            <i class="size-3" data-lucide="clock"></i> Recent
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-md border border-default-200 bg-default-100 px-2 py-0.5 text-xs font-medium text-default-500">
                                            <i class="size-3" data-lucide="archive"></i> Archived
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" data-quick-view="{{ $data->id }}"
                                            class="inline-grid size-8 place-items-center rounded-md text-default-500 hover:bg-default-150 hover:text-default-800"
                                            title="Quick preview" aria-label="Quick preview of {{ $data->date_en }}">
                                            <i class="size-4" data-lucide="eye"></i>
                                        </button>
                                        <a href="{{ route('draw_results.show', $data->id) }}"
                                            class="inline-grid size-8 place-items-center rounded-md text-default-500 hover:bg-default-150 hover:text-default-800"
                                            title="Full details" aria-label="Full details for {{ $data->date_en }}">
                                            <i class="size-4" data-lucide="arrow-right"></i>
                                        </a>
                                        @if ($data->endpoint)
                                            <a href="{{ $data->endpoint }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-grid size-8 place-items-center rounded-md text-default-500 hover:bg-default-150 hover:text-default-800"
                                                title="API source" aria-label="API source">
                                                <i class="size-4" data-lucide="external-link"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12">
                                    <x-ui.empty-state title="No draw results found"
                                        description="Adjust the filters above, or sync the latest result from the Government Lottery Office." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>

            @if ($results->count())
                <div class="card-footer">
                    <p class="text-default-500 text-sm">
                        Showing <b>{{ $results->firstItem() }}</b>–<b>{{ $results->lastItem() }}</b>
                        of <b>{{ $results->total() }}</b>
                    </p>
                    <nav aria-label="Pagination" class="flex items-center gap-2">
                        {{ $results->withQueryString()->links() }}
                    </nav>
                </div>
            @endif
        </div>
    </div>

    {{-- ---------- Sync overlay ---------- --}}
    <div id="syncOverlay" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-default-950/60 p-4"
        role="alertdialog" aria-live="assertive" aria-labelledby="syncOverlayText">
        <div class="w-full max-w-sm rounded-2xl bg-card p-8 text-center shadow-2xl">
            <svg class="mx-auto size-12 animate-spin text-primary" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                <path class="opacity-90" fill="currentColor"
                    d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4Zm2 5.3A8 8 0 0 1 4 12H0c0 3 1.1 5.8 3 7.9l3-2.6Z" />
            </svg>
            <p id="syncOverlayText" class="mt-5 text-lg font-semibold text-default-900">Syncing…</p>
            <p class="mt-1.5 text-sm text-default-500">
                This calls the Government Lottery Office API. Please keep this window open.
            </p>
        </div>
    </div>

    {{-- ---------- Quick view ---------- --}}
    <div id="quickView" class="fixed inset-0 z-[9998] hidden items-center justify-center bg-default-950/60 p-4"
        role="dialog" aria-modal="true" aria-labelledby="quickViewTitle">
        <div class="flex max-h-[88vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-card shadow-2xl">
            <div class="flex items-center justify-between gap-3 border-b border-default-200 px-6 py-4">
                <div>
                    <h3 id="quickViewTitle" class="text-lg font-bold text-default-900">Draw result</h3>
                    <p id="quickViewSub" class="text-xs text-default-500"></p>
                </div>
                <button type="button" data-quick-close
                    class="inline-grid size-9 place-items-center rounded-lg text-default-500 hover:bg-default-150 hover:text-default-800"
                    aria-label="Close">
                    <i class="size-5" data-lucide="x"></i>
                </button>
            </div>

            <div id="quickViewBody" class="flex-1 overflow-y-auto px-6 py-5"></div>

            <div class="flex justify-end gap-2 border-t border-default-200 bg-default-100 px-6 py-3">
                <button type="button" data-quick-close
                    class="btn btn-sm bg-default-100 text-default-700 hover:bg-default-200">Close</button>
                <a id="quickViewLink" href="#" class="btn btn-sm bg-primary text-white hover:bg-primary/90">
                    <i class="size-4 me-1.5" data-lucide="arrow-right"></i>Full details
                </a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const overlay = document.getElementById('syncOverlay');
            const overlayText = document.getElementById('syncOverlayText');
            const modal = document.getElementById('quickView');
            const body = document.getElementById('quickViewBody');

            const show = (el) => {
                el.classList.remove('hidden');
                el.classList.add('flex');
                document.body.style.overflow = 'hidden';
            };
            const hide = (el) => {
                el.classList.add('hidden');
                el.classList.remove('flex');
                document.body.style.overflow = '';
            };

            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            })[c]);
            const money = (v) => Number.parseInt(v, 10).toLocaleString('en-US');

            /* ---------- sync forms ---------- */
            document.querySelectorAll('form[data-sync]').forEach((form) => {
                form.addEventListener('submit', () => {
                    overlayText.textContent = form.dataset.syncMessage || 'Syncing…';
                    show(overlay);
                });
            });

            /* ---------- quick view ---------- */
            // One renderer for both prize tiers and running numbers — the two
            // payloads share a shape, so they no longer need separate markup.
            function group(title, items, accent) {
                if (!items || !items.length) return '';
                const rows = items.map((item) => {
                    const numbers = Array.isArray(item.number) ? item.number : [item.number];
                    const chip = accent && item.name === 'First Prize'
                        ? 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-400'
                        : 'border-default-200 bg-default-100 text-default-800';
                    return `
                        <div class="rounded-xl border border-default-200 p-4">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <h4 class="font-semibold text-default-900">${esc(item.name)}</h4>
                                <span class="rounded-md border border-green-500/25 bg-green-500/10 px-2 py-0.5 text-xs font-medium text-green-600 dark:text-green-400">฿${money(item.reward)}</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                ${numbers.map((n) => `<span class="rounded-md border ${chip} px-2.5 py-1 font-mono text-sm font-bold tabular-nums tracking-wider">${esc(n)}</span>`).join('')}
                            </div>
                            ${numbers.length > 5 ? `<p class="mt-3 border-t border-default-200 pt-2 text-xs text-default-500">${numbers.length} winning numbers</p>` : ''}
                        </div>`;
                }).join('');

                return `<h3 class="mb-3 mt-1 text-xs font-semibold uppercase tracking-wide text-default-500">${esc(title)}</h3>
                        <div class="space-y-3 mb-6">${rows}</div>`;
            }

            document.querySelectorAll('[data-quick-view]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.quickView;
                    overlayText.textContent = 'Loading draw details…';
                    show(overlay);

                    try {
                        const res = await fetch(`/draw_results/${id}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        if (!res.ok) throw new Error(res.statusText);
                        const data = await res.json();

                        document.getElementById('quickViewTitle').textContent = data.date_en || 'Draw result';
                        document.getElementById('quickViewSub').textContent = data.date_th || '';
                        document.getElementById('quickViewLink').href = `/draw_results/${id}/detail`;
                        body.innerHTML =
                            group('Prize categories', data.prizes, true) +
                            group('Running numbers', data.running_numbers, false);

                        hide(overlay);
                        show(modal);
                        if (window.lucide) window.lucide.createIcons();
                    } catch (err) {
                        hide(overlay);
                        alert('Could not load draw details. Please try again.');
                    }
                });
            });

            document.querySelectorAll('[data-quick-close]').forEach((b) =>
                b.addEventListener('click', () => hide(modal)));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) hide(modal);
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) hide(modal);
            });
        })();
    </script>
@endsection
