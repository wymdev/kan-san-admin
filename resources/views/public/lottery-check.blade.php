@extends('public.lottery.layout')

@section('title', 'Check Your Numbers')
@section('description', 'Check Thai Government Lottery numbers against the official draw results.')

@push('styles')
    <style>
        .check-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 350px;
            gap: 1.25rem;
            align-items: start;
        }

        @media (max-width: 940px) {
            .check-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            /* On narrow screens the reason you came here goes first. */
            .check-panel {
                order: -1;
            }
        }

        .check-panel {
            position: sticky;
            top: 76px;
        }

        @media (max-width: 940px) {
            .check-panel {
                position: static;
            }
        }

        /* ---------- Draw summary ---------- */
        .draw-meta {
            text-align: center;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--line);
            margin-bottom: 1.25rem;
        }

        .draw-meta .date {
            font-size: clamp(1.25rem, 3.5vw, 1.625rem);
            font-weight: 800;
        }

        .draw-meta .date-th {
            margin-top: .125rem;
            color: var(--text-muted);
            font-size: .875rem;
        }

        .first-prize {
            text-align: center;
            padding: clamp(1.25rem, 4vw, 1.75rem) 1rem;
            border-radius: var(--r-md);
            background: linear-gradient(180deg, var(--gold-soft), rgba(251, 191, 36, .03));
            border: 1px solid var(--gold-line);
        }

        .prize-caption {
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--text-dim);
        }

        .prize-reward {
            margin-top: .5rem;
            font-size: .875rem;
            font-weight: 600;
            color: var(--green);
        }

        .running-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
            margin-top: .75rem;
        }

        @media (max-width: 560px) {
            .running-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .running-box {
            padding: 1rem .75rem;
            border-radius: var(--r-md);
            background: var(--surface-2);
            border: 1px solid var(--line);
            text-align: center;
        }

        .running-nums {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            justify-content: center;
            margin: .5rem 0;
        }

        /* ---------- Collapsible prize tiers ---------- */
        .tier {
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            background: var(--bg-elevated);
            overflow: hidden;
        }

        .tier+.tier {
            margin-top: .5rem;
        }

        .tier-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .875rem 1rem;
            background: none;
            border: 0;
            color: inherit;
            font: inherit;
            text-align: left;
            cursor: pointer;
        }

        .tier-toggle:hover {
            background: rgba(255, 255, 255, .03);
        }

        .tier-name {
            font-weight: 600;
        }

        .tier-meta {
            display: flex;
            align-items: center;
            gap: .625rem;
            color: var(--text-dim);
            font-size: .8125rem;
        }

        .tier-toggle .caret {
            transition: transform .2s;
            flex: none;
        }

        .tier-toggle[aria-expanded="true"] .caret {
            transform: rotate(180deg);
        }

        .tier-body {
            padding: 0 1rem 1rem;
        }

        .tier-nums {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(82px, 1fr));
            gap: .5rem;
        }

        /* ---------- Check form ---------- */
        .field-label {
            display: block;
            font-size: .8125rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: .625rem;
        }

        .num-rows {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            margin-bottom: .625rem;
        }

        .num-row {
            display: flex;
            gap: .5rem;
        }

        .num-input {
            flex: 1;
            min-width: 0;
            height: 52px;
            padding: 0 .75rem;
            background: var(--bg);
            border: 1.5px solid var(--line-strong);
            border-radius: var(--r-sm);
            color: var(--text);
            font: inherit;
            font-size: 1.375rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            letter-spacing: .2em;
            text-align: center;
            transition: border-color .15s;
        }

        .num-input::placeholder {
            color: #334155;
            letter-spacing: .2em;
        }

        .num-input:focus {
            outline: none;
            border-color: var(--gold);
        }

        .row-remove {
            flex: none;
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            background: transparent;
            border: 1.5px solid var(--line-strong);
            border-radius: var(--r-sm);
            color: var(--text-dim);
            cursor: pointer;
            transition: color .15s, border-color .15s;
        }

        .row-remove:hover {
            color: var(--red);
            border-color: var(--red);
        }

        .add-row {
            width: 100%;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            background: transparent;
            border: 1.5px dashed var(--line-strong);
            border-radius: var(--r-sm);
            color: var(--text-dim);
            font: inherit;
            font-size: .875rem;
            font-weight: 500;
            cursor: pointer;
        }

        .add-row:hover {
            border-color: var(--text-dim);
            color: var(--text);
        }

        .form-hint {
            margin-top: .75rem;
            font-size: .75rem;
            color: var(--text-dim);
            text-align: center;
        }

        .form-error {
            display: none;
            margin-top: .625rem;
            padding: .5rem .75rem;
            border-radius: var(--r-sm);
            background: rgba(248, 113, 113, .12);
            border: 1px solid rgba(248, 113, 113, .3);
            color: var(--red);
            font-size: .8125rem;
        }

        .form-error.is-shown {
            display: block;
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ---------- Result dialog ---------- */
        .sheet {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(2, 6, 16, .75);
            backdrop-filter: blur(4px);
        }

        .sheet.is-open {
            display: flex;
        }

        .sheet-card {
            width: 100%;
            max-width: 520px;
            max-height: min(85vh, 720px);
            display: flex;
            flex-direction: column;
            background: var(--surface);
            border: 1px solid var(--line-strong);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow);
        }

        .sheet-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--line);
        }

        .sheet-head h2 {
            font-size: 1.0625rem;
        }

        .sheet-close {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            background: transparent;
            border: 1px solid var(--line-strong);
            border-radius: var(--r-sm);
            color: var(--text-muted);
            cursor: pointer;
        }

        .sheet-close:hover {
            background: var(--surface-2);
            color: var(--text);
        }

        .sheet-body {
            padding: 1.25rem;
            overflow-y: auto;
        }

        .result-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: .875rem 1rem;
            border-radius: var(--r-md);
            background: var(--bg-elevated);
            border: 1px solid var(--line);
            border-left: 3px solid var(--text-dim);
        }

        .result-row+.result-row {
            margin-top: .5rem;
        }

        .result-row.is-win {
            border-left-color: var(--gold);
            background: linear-gradient(90deg, var(--gold-soft), var(--bg-elevated) 60%);
        }

        .result-prizes {
            margin-top: .5rem;
            display: flex;
            flex-direction: column;
            gap: .25rem;
            font-size: .8125rem;
            color: var(--text-muted);
        }

        .result-prizes b {
            color: var(--green);
        }

        .result-total {
            margin-top: .5rem;
            padding-top: .5rem;
            border-top: 1px dashed var(--line-strong);
            font-weight: 700;
            color: var(--gold);
            font-size: .875rem;
        }

        .result-verdict {
            flex: none;
            text-align: right;
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <span class="eyebrow"><x-icon name="sparkles" :size="13" /> Official results</span>
        <h1 class="page-title">Check your lottery numbers</h1>
        <p class="page-sub">Enter any 6-digit ticket number and we'll match it against every prize tier in the selected
            draw.</p>
    </div>

    <div class="check-layout">
        {{-- ---------- Results ---------- --}}
        <section class="card">
            <div class="card-head">
                <h2><x-icon name="trophy" :size="18" /> Draw results</h2>
                @if ($latestDraw)
                    <a href="{{ route('public.lottery-result', $latestDraw->draw_date->format('Y-m-d')) }}" class="btn-link">
                        Full breakdown <x-icon name="chevron-right" :size="14" />
                    </a>
                @endif
            </div>

            @if ($latestDraw)
                @if ($drawDates->isNotEmpty())
                    <div class="card-body" style="padding-bottom:0">
                        <div class="chip-row" role="tablist" aria-label="Draw dates">
                            @foreach ($drawDates as $draw)
                                @php $d = $draw->draw_date->format('Y-m-d'); @endphp
                                <a href="{{ route('public.lottery-check', ['date' => $d]) }}"
                                    class="chip {{ $d === $latestDraw->draw_date->format('Y-m-d') ? 'is-active' : '' }}">
                                    {{ $draw->date_en ?? $draw->draw_date->format('d M Y') }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="card-body">
                    <div class="draw-meta">
                        <span class="prize-caption">Draw date</span>
                        <div class="date">{{ $latestDraw->date_en ?? $latestDraw->draw_date->format('d F Y') }}</div>
                        @if ($latestDraw->date_th)
                            <div class="date-th">{{ $latestDraw->date_th }}</div>
                        @endif
                    </div>

                    @php
                        $prizes = $latestDraw->normalized_prizes ?? [];
                        $firstPrize = $prizes['first_prize'] ?? ($prizes['prizeFirst'] ?? ($prizes['prize_1'] ?? null));
                        $firstPrize = is_string($firstPrize) ? [$firstPrize] : (array) $firstPrize;

                        $running = collect($latestDraw->running_numbers ?? []);
                        $pick = function (string $needle) use ($running) {
                            $hit = $running->first(fn($i) => isset($i['id']) && stripos($i['id'], $needle) !== false);
                            $nums = $hit['number'] ?? [];
                            return is_string($nums) ? [$nums] : (array) $nums;
                        };
                        $front3 = $pick('FrontThree');
                        $back3 = $pick('BackThree');
                        $back2 = $pick('BackTwo');
                    @endphp

                    <div class="first-prize">
                        <span class="prize-caption">First prize</span>
                        <div class="num num-hero">{{ $firstPrize[0] ?? '——————' }}</div>
                        <div class="prize-reward">฿6,000,000</div>
                    </div>

                    <div class="running-grid">
                        @foreach ([['Front 3 digits', $front3, '฿4,000'], ['Back 3 digits', $back3, '฿4,000'], ['Last 2 digits', $back2, '฿2,000']] as [$label, $nums, $reward])
                            <div class="running-box">
                                <span class="prize-caption">{{ $label }}</span>
                                <div class="running-nums">
                                    @forelse ($nums as $n)
                                        <span class="num num-lg">{{ $n }}</span>
                                    @empty
                                        <span class="num num-lg" style="color:var(--text-dim)">—</span>
                                    @endforelse
                                </div>
                                <div class="prize-reward">{{ $reward }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div style="margin-top:1.25rem">
                        @foreach ([['2nd prize', ['second_prize', 'prizeSecond'], '200,000'], ['3rd prize', ['third_prize', 'prizeThird'], '80,000'], ['4th prize', ['fourth_prize', 'prizeForth'], '40,000'], ['5th prize', ['fifth_prize', 'prizeFifth'], '20,000']] as $i => [$label, $keys, $reward])
                            @php
                                $nums = [];
                                foreach ($keys as $k) {
                                    if (!empty($prizes[$k])) {
                                        $nums = (array) $prizes[$k];
                                        break;
                                    }
                                }
                            @endphp
                            @if ($nums)
                                <div class="tier">
                                    <button type="button" class="tier-toggle" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}"
                                        aria-controls="tier-{{ $i }}">
                                        <span class="tier-name">{{ $label }}</span>
                                        <span class="tier-meta">
                                            <span>฿{{ $reward }}</span>
                                            <span>{{ count($nums) }} numbers</span>
                                            <x-icon name="chevron-down" :size="16" class="caret" />
                                        </span>
                                    </button>
                                    <div class="tier-body" id="tier-{{ $i }}" @if ($i !== 0) hidden @endif>
                                        <div class="tier-nums">
                                            @foreach ($nums as $n)
                                                <span class="num num-chip">{{ $n }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <div class="empty">
                    <x-icon name="inbox" :size="40" />
                    <p class="empty-title">No draw results yet</p>
                    <p>Results appear here once the next draw is published.</p>
                </div>
            @endif
        </section>

        {{-- ---------- Check form ---------- --}}
        <section class="card check-panel">
            <div class="card-head">
                <h2><x-icon name="scan-line" :size="18" /> Check numbers</h2>
            </div>
            <div class="card-body">
                <form id="checkForm" novalidate>
                    <label class="field-label" for="num-0">Your 6-digit numbers</label>

                    <div class="num-rows" id="numRows">
                        <div class="num-row">
                            <input id="num-0" class="num-input" type="text" inputmode="numeric" pattern="[0-9]*"
                                maxlength="6" placeholder="000000" autocomplete="off" aria-label="Lottery number 1">
                        </div>
                    </div>

                    <button type="button" class="add-row" id="addRow">
                        <x-icon name="plus" :size="15" /> Add another number
                    </button>

                    <p class="form-error" id="formError" role="alert"></p>

                    <button type="submit" class="btn btn-primary btn-block" id="checkBtn" style="margin-top:.75rem"
                        @disabled(!$latestDraw)>
                        <x-icon name="scan-line" :size="18" /> <span>Check now</span>
                    </button>

                    <p class="form-hint">Checked against the draw shown on the left.</p>
                </form>
            </div>
        </section>
    </div>

    {{-- ---------- Result dialog ---------- --}}
    <div class="sheet" id="resultSheet" role="dialog" aria-modal="true" aria-labelledby="sheetTitle">
        <div class="sheet-card">
            <div class="sheet-head">
                <h2 id="sheetTitle">Your results</h2>
                <button type="button" class="sheet-close" id="sheetClose" aria-label="Close results">
                    <x-icon name="x" :size="18" />
                </button>
            </div>
            <div class="sheet-body">
                <div class="stat-grid" id="sheetSummary" style="margin-bottom:1.25rem"></div>
                <div id="sheetResults"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('checkForm');
            const rows = document.getElementById('numRows');
            const addRow = document.getElementById('addRow');
            const checkBtn = document.getElementById('checkBtn');
            const errorBox = document.getElementById('formError');
            const sheet = document.getElementById('resultSheet');
            const summaryEl = document.getElementById('sheetSummary');
            const resultsEl = document.getElementById('sheetResults');
            const drawDate = @json($latestDraw?->draw_date?->format('Y-m-d') ?? '');
            const endpoint = @json(route('public.lottery-check.submit'));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            })[c]);

            const iconX = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>`;

            /* ---------- collapsible prize tiers ---------- */
            document.querySelectorAll('.tier-toggle').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const open = btn.getAttribute('aria-expanded') === 'true';
                    btn.setAttribute('aria-expanded', String(!open));
                    document.getElementById(btn.getAttribute('aria-controls')).hidden = open;
                });
            });

            /* ---------- number inputs ---------- */
            function bind(input) {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '').slice(0, 6);
                    hideError();
                });
                input.addEventListener('keydown', (e) => {
                    if (e.key !== 'Enter') return;
                    e.preventDefault();
                    if (input.value.length === 6) append();
                    else form.requestSubmit();
                });
            }

            function append() {
                const row = document.createElement('div');
                row.className = 'num-row';
                const index = rows.children.length + 1;
                row.innerHTML =
                    `<input class="num-input" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="000000" autocomplete="off" aria-label="Lottery number ${index}">` +
                    `<button type="button" class="row-remove" aria-label="Remove number ${index}">${iconX}</button>`;
                rows.appendChild(row);
                row.querySelector('.row-remove').addEventListener('click', () => row.remove());
                const input = row.querySelector('input');
                bind(input);
                input.focus();
            }

            rows.querySelectorAll('.num-input').forEach(bind);
            addRow.addEventListener('click', append);

            function showError(msg) {
                errorBox.textContent = msg;
                errorBox.classList.add('is-shown');
            }

            function hideError() {
                errorBox.classList.remove('is-shown');
            }

            /* ---------- dialog ---------- */
            function closeSheet() {
                sheet.classList.remove('is-open');
                document.body.style.overflow = '';
            }

            document.getElementById('sheetClose').addEventListener('click', closeSheet);
            sheet.addEventListener('click', (e) => {
                if (e.target === sheet) closeSheet();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && sheet.classList.contains('is-open')) closeSheet();
            });

            const baht = (n) => '฿' + n.toLocaleString('en-US');
            const toNumber = (reward) => parseInt(String(reward).replace(/[^0-9]/g, ''), 10) || 0;

            /* ---------- submit ---------- */
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                hideError();

                const numbers = Array.from(rows.querySelectorAll('.num-input'))
                    .map((i) => i.value.trim())
                    .filter((v) => v.length === 6);

                if (!numbers.length) {
                    showError('Enter at least one complete 6-digit number.');
                    return;
                }

                const label = checkBtn.querySelector('span');
                const original = label.textContent;
                checkBtn.disabled = true;
                label.textContent = 'Checking…';
                checkBtn.querySelector('svg').classList.add('spin');

                try {
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ numbers: numbers.join(','), draw_date: drawDate }),
                    });
                    const data = await res.json();

                    if (!data.success || !data.results) {
                        showError(data.error || 'Could not check those numbers. Please try again.');
                        return;
                    }

                    const won = data.results.filter((r) => r.won);
                    const total = data.results.reduce((sum, r) =>
                        sum + (r.prizes || []).reduce((s, p) => s + toNumber(p.reward), 0), 0);

                    summaryEl.innerHTML = `
                        <div class="stat">
                            <div class="stat-value">${data.results.length}</div>
                            <div class="stat-label">Checked</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value" style="color:var(--green)">${won.length}</div>
                            <div class="stat-label">Winners</div>
                        </div>
                        <div class="stat" style="border-color:var(--gold-line);background:var(--gold-soft)">
                            <div class="stat-value" style="color:var(--gold)">${baht(total)}</div>
                            <div class="stat-label">Total won</div>
                        </div>`;

                    resultsEl.innerHTML = data.results.map((r) => {
                        const prizes = r.prizes || [];
                        const sum = prizes.reduce((s, p) => s + toNumber(p.reward), 0);
                        return `
                            <div class="result-row ${r.won ? 'is-win' : ''}">
                                <div>
                                    <div class="num num-lg">${esc(r.number)}</div>
                                    ${r.won ? `
                                        <div class="result-prizes">
                                            ${prizes.map((p) => `<span>${esc(p.name)} · <b>฿${esc(p.reward)}</b></span>`).join('')}
                                        </div>
                                        ${prizes.length > 1 ? `<div class="result-total">Total ${baht(sum)}</div>` : ''}
                                    ` : ''}
                                </div>
                                <div class="result-verdict">
                                    <span class="badge ${r.won ? 'badge-gold' : ''}">${r.won ? 'Winner' : 'No win'}</span>
                                </div>
                            </div>`;
                    }).join('');

                    document.getElementById('sheetTitle').textContent =
                        won.length ? `${won.length} winning ${won.length === 1 ? 'number' : 'numbers'}` : 'No wins this draw';
                    sheet.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                    document.getElementById('sheetClose').focus();
                } catch (err) {
                    showError('Network error. Please check your connection and try again.');
                } finally {
                    checkBtn.disabled = false;
                    label.textContent = original;
                    checkBtn.querySelector('svg').classList.remove('spin');
                }
            });
        })();
    </script>
@endpush
