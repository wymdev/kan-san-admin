<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check Lottery Results | Thai Lottery</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --gold: #F59E0B;
            --gold-light: #FBBF24;
            --green: #22C55E;
            --green-light: #4ADE80;
            --red: #EF4444;
            --dark: #0F172A;
            --dark-card: #1E293B;
            --dark-light: #334155;
            --gray: #64748B;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--dark);
            color: #F8FAFC;
            min-height: 100vh;
        }
        
        /* Navbar */
        .navbar {
            background: var(--dark-card);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--gold);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .brand-icon svg { color: #78350f; }
        
        .brand-text {
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .nav-date { color: var(--gray); font-size: 0.9rem; }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        
        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 1.5rem;
            align-items: start;
        }
        
        /* Cards */
        .card {
            background: var(--dark-card);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--dark-light);
        }
        
        .card-header svg { color: var(--gold); width: 20px; height: 20px; }
        .card-header h2 { font-size: 1rem; font-weight: 600; }
        
        .card-body { padding: 1.5rem; }
        
        /* History Chips */
        .history-scroll {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 1rem 1.25rem;
            background: rgba(0, 0, 0, 0.2);
        }
        
        .history-scroll::-webkit-scrollbar { display: none; }
        
        .history-chip {
            flex-shrink: 0;
            padding: 0.5rem 1rem;
            background: var(--dark-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--gray);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .history-chip:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .history-chip.active {
            background: var(--green);
            color: #052E16;
            border-color: var(--green);
        }
        
        /* Draw Header */
        .draw-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .draw-label {
            color: var(--gold);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.25rem;
        }
        
        .draw-date {
            font-size: 1.75rem;
            font-weight: 800;
        }
        
        /* First Prize */
        .first-prize {
            background: var(--dark-light);
            border: 2px solid var(--gold);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .prize-label {
            font-size: 0.75rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        
        .prize-number {
            font-size: 3rem;
            font-weight: 900;
            letter-spacing: 0.15em;
            color: var(--gold);
        }
        
        .prize-reward {
            margin-top: 0.75rem;
            color: var(--green);
            font-weight: 600;
        }
        
        /* Prize Grid */
        .prizes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .prize-box {
            background: var(--dark-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.25rem 1rem;
            text-align: center;
        }
        
        .prize-box .prize-number {
            font-size: 1.5rem;
            color: white;
        }
        
        .prize-box .prize-reward {
            font-size: 0.8rem;
        }
        
        .number-group {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        /* Prize Lists */
        .prize-lists {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .prize-list-item {
            background: var(--dark);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .prize-list-header {
            padding: 0.875rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        
        .prize-list-header:hover { background: rgba(255, 255, 255, 0.03); }
        
        .prize-list-title span { font-weight: 600; }
        .prize-list-title .reward { color: var(--green); font-size: 0.85rem; margin-left: 0.5rem; }
        
        .prize-list-header svg { color: var(--gray); transition: transform 0.3s; }
        .prize-list-header.open svg { transform: rotate(180deg); }
        
        .prize-list-content { display: none; padding: 0 1rem 1rem; }
        .prize-list-content.open { display: block; }
        
        .prize-list-numbers {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(85px, 1fr));
            gap: 0.5rem;
        }
        
        .small-number {
            font-size: 0.95rem;
            font-weight: 600;
            color: #CBD5E1;
            text-align: center;
            background: var(--dark-card);
            padding: 0.4rem;
            border-radius: 6px;
            letter-spacing: 0.1em;
        }
        
        /* Check Form */
        .check-card { position: sticky; top: 5rem; }
        
        .form-label {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 0.75rem;
            display: block;
        }
        
        .input-container {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }
        
        .input-row {
            display: flex;
            gap: 0.5rem;
            width: 100%;
        }
        
        .lottery-input {
            flex: 1;
            min-width: 0;
            background: var(--dark);
            border: 2px solid var(--dark-light);
            border-radius: 10px;
            color: white;
            padding: 0.75rem 0.5rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-align: center;
        }
        
        .lottery-input:focus {
            outline: none;
            border-color: var(--gold);
        }
        
        .lottery-input::placeholder {
            color: var(--dark-light);
            font-size: 1rem;
        }
        
        .remove-btn {
            width: 44px;
            height: 44px;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid var(--red);
            border-radius: 10px;
            color: var(--red);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .remove-btn:hover { background: var(--red); color: white; }
        
        .add-btn {
            width: 100%;
            background: transparent;
            border: 2px dashed var(--dark-light);
            border-radius: 10px;
            color: var(--gray);
            padding: 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .add-btn:hover { border-color: var(--gray); color: white; }
        
        .check-btn {
            width: 100%;
            background: var(--gold);
            border: none;
            border-radius: 10px;
            color: #78350f;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
        
        .check-btn:hover { background: var(--gold-light); }
        .check-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        
        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .modal-overlay.open { display: flex; }
        
        .modal {
            background: var(--dark-card);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            width: 100%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
            animation: modalSlide 0.3s ease-out;
        }
        
        @keyframes modalSlide {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--dark-light);
        }
        
        .modal-header h3 { font-size: 1.1rem; font-weight: 700; }
        
        .modal-close {
            width: 36px;
            height: 36px;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: var(--gray);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-close:hover { background: rgba(255, 255, 255, 0.1); color: white; }
        
        .modal-body {
            padding: 1.5rem;
            max-height: calc(80vh - 70px);
            overflow-y: auto;
        }
        
        .modal-summary {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .summary-box {
            flex: 1;
            background: var(--dark);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
        }
        
        .summary-box .value {
            font-size: 2rem;
            font-weight: 800;
        }
        
        .summary-box .label {
            font-size: 0.75rem;
            color: var(--gray);
            text-transform: uppercase;
        }
        
        .summary-box.won .value { color: var(--green); }
        .summary-box.lost .value { color: var(--gray); }
        
        .result-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .result-item {
            background: var(--dark);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border-left: 4px solid var(--gray);
        }
        
        .result-item.won {
            border-left-color: var(--green);
            background: rgba(34, 197, 94, 0.1);
        }
        
        .result-number {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            margin-bottom: 0.25rem;
        }
        
        .result-status {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .result-status.won { color: var(--green); }
        .result-status.lost { color: var(--gray); }
        
        .result-prizes {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--green);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--gray);
        }
        
        .empty-state svg { margin-bottom: 0.75rem; opacity: 0.5; }
        
        /* Responsive */
        @media (max-width: 900px) {
            .main-grid { grid-template-columns: 1fr; }
            .check-card { position: static; order: -1; }
            .prizes-grid { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 480px) {
            .container { padding: 1rem; }
            .prize-number { font-size: 2.5rem; }
            .navbar { padding: 0.875rem 1rem; }
            .modal { max-height: 90vh; }
        }
        
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="brand">
            <div class="brand-icon">
                <i data-lucide="crown" size="22"></i>
            </div>
            <span class="brand-text">Thai Lottery</span>
        </div>
        <div class="nav-date">{{ date('l, d F Y') }}</div>
    </nav>

    <div class="container">
        <div class="main-grid">
            
            <!-- Results Section -->
            <div class="card">
                <div class="card-header">
                    <i data-lucide="trophy"></i>
                    <h2>Official Results</h2>
                </div>
                
                @if($latestDraw)
                    <!-- History -->
                    <div class="history-scroll">
                        @foreach($drawDates as $draw)
                            <a href="{{ route('public.lottery-check', ['date' => $draw->draw_date->format('Y-m-d')]) }}" 
                               class="history-chip {{ (request('date') == $draw->draw_date->format('Y-m-d') || (!request('date') && $loop->first)) ? 'active' : '' }}">
                                {{ $draw->date_en ?? $draw->draw_date->format('d M Y') }}
                            </a>
                        @endforeach
                    </div>
                    
                    <div class="card-body">
                        <!-- Draw Header -->
                        <div class="draw-header">
                            <div class="draw-label">Draw Date</div>
                            <div class="draw-date">{{ $latestDraw->date_en ?? $latestDraw->draw_date->format('d F Y') }}</div>
                        </div>
                        
                        <!-- First Prize -->
                        @php
                            $p = $latestDraw->normalized_prizes ?? [];
                            $firstPrizeNumbers = $p['first_prize'] ?? $p['prizeFirst'] ?? $p['prize_1'] ?? null;
                            if (is_string($firstPrizeNumbers)) $firstPrizeNumbers = [$firstPrizeNumbers];
                        @endphp
                        <div class="first-prize">
                            <div class="prize-label">🏆 First Prize</div>
                            <div class="prize-number">{{ $firstPrizeNumbers[0] ?? 'XXXXXX' }}</div>
                            <div class="prize-reward">Reward: ฿6,000,000</div>
                        </div>
                        
                        <!-- Minor Prizes -->
                        <div class="prizes-grid">
                            @php 
                                $front3 = collect($latestDraw->running_numbers)->first(fn($i) => isset($i['id']) && (stripos($i['id'], 'Front') !== false));
                                $front3Nums = $front3['number'] ?? ['XXX', 'XXX'];
                                if(is_string($front3Nums)) $front3Nums = [$front3Nums];
                            @endphp
                            <div class="prize-box">
                                <div class="prize-label">Front 3 Digits</div>
                                <div class="number-group">
                                    @foreach($front3Nums as $num)
                                        <div class="prize-number">{{ $num }}</div>
                                    @endforeach
                                </div>
                                <div class="prize-reward">฿4,000</div>
                            </div>
                            
                            @php 
                                $rear3 = collect($latestDraw->running_numbers)->first(fn($i) => isset($i['id']) && (stripos($i['id'], 'BackThree') !== false));
                                $rear3Nums = $rear3['number'] ?? ['XXX', 'XXX'];
                                if(is_string($rear3Nums)) $rear3Nums = [$rear3Nums];
                            @endphp
                            <div class="prize-box">
                                <div class="prize-label">Back 3 Digits</div>
                                <div class="number-group">
                                    @foreach($rear3Nums as $num)
                                        <div class="prize-number">{{ $num }}</div>
                                    @endforeach
                                </div>
                                <div class="prize-reward">฿4,000</div>
                            </div>
                            
                            @php 
                                $rear2 = collect($latestDraw->running_numbers)->first(fn($i) => isset($i['id']) && (stripos($i['id'], 'BackTwo') !== false));
                                $rear2Nums = $rear2['number'] ?? ['XX'];
                                if(is_string($rear2Nums)) $rear2Nums = [$rear2Nums];
                            @endphp
                            <div class="prize-box">
                                <div class="prize-label">Last 2 Digits</div>
                                <div class="prize-number">{{ $rear2Nums[0] ?? 'XX' }}</div>
                                <div class="prize-reward">฿2,000</div>
                            </div>
                        </div>
                        
                        <!-- Prize Lists -->
                        <div class="prize-lists">
                            @foreach([
                                ['keys' => ['second_prize', 'prizeSecond'], 'name' => '2nd Prize', 'reward' => '200,000'],
                                ['keys' => ['third_prize', 'prizeThird'], 'name' => '3rd Prize', 'reward' => '80,000'],
                                ['keys' => ['fourth_prize', 'prizeForth'], 'name' => '4th Prize', 'reward' => '40,000'],
                                ['keys' => ['fifth_prize', 'prizeFifth'], 'name' => '5th Prize', 'reward' => '20,000']
                            ] as $meta)
                                @php 
                                    $prizeNums = [];
                                    foreach($meta['keys'] as $k) {
                                        if(isset($latestDraw->normalized_prizes[$k])) {
                                            $prizeNums = $latestDraw->normalized_prizes[$k];
                                            break;
                                        }
                                    }
                                @endphp
                                @if(!empty($prizeNums))
                                    <div class="prize-list-item">
                                        <div class="prize-list-header" onclick="this.classList.toggle('open'); this.nextElementSibling.classList.toggle('open');">
                                            <div class="prize-list-title">
                                                <span>{{ $meta['name'] }}</span>
                                                <span class="reward">฿{{ $meta['reward'] }}</span>
                                            </div>
                                            <i data-lucide="chevron-down" size="16"></i>
                                        </div>
                                        <div class="prize-list-content open">
                                            <div class="prize-list-numbers">
                                                @foreach($prizeNums as $num)
                                                    <div class="small-number">{{ $num }}</div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="empty-state">
                        <i data-lucide="inbox" size="48"></i>
                        <p>No draw results available</p>
                    </div>
                @endif
            </div>
            
            <!-- Check Form -->
            <div class="card check-card">
                <div class="card-header">
                    <i data-lucide="search"></i>
                    <h2>Check Numbers</h2>
                </div>
                
                <div class="card-body">
                    <form id="checkForm">
                        <label class="form-label">Enter 6-digit lottery numbers:</label>
                        
                        <div class="input-container" id="inputContainer">
                            <div class="input-row">
                                <input type="tel" class="lottery-input" maxlength="6" placeholder="000000" autofocus>
                            </div>
                        </div>
                        
                        <button type="button" class="add-btn" id="addBtn">
                            <i data-lucide="plus" size="16"></i>
                            Add Number
                        </button>
                        
                        <input type="hidden" id="drawDate" value="{{ $latestDraw ? $latestDraw->draw_date->format('Y-m-d') : '' }}">
                        
                        <button type="submit" class="check-btn" id="checkBtn">
                            <i data-lucide="scan-line" size="20"></i>
                            Check Now
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
    
    <!-- Results Modal -->
    <div class="modal-overlay" id="resultModal">
        <div class="modal">
            <div class="modal-header">
                <h3>🎰 Check Results</h3>
                <button class="modal-close" onclick="closeModal()">
                    <i data-lucide="x" size="18"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-summary" id="modalSummary"></div>
                <div class="result-list" id="resultList"></div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        const form = document.getElementById('checkForm');
        const checkBtn = document.getElementById('checkBtn');
        const inputContainer = document.getElementById('inputContainer');
        const addBtn = document.getElementById('addBtn');
        const resultModal = document.getElementById('resultModal');
        const resultList = document.getElementById('resultList');
        const modalSummary = document.getElementById('modalSummary');
        
        function addInput() {
            const row = document.createElement('div');
            row.className = 'input-row';
            row.innerHTML = `
                <input type="tel" class="lottery-input" maxlength="6" placeholder="000000">
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">
                    <i data-lucide="x" size="18"></i>
                </button>
            `;
            inputContainer.appendChild(row);
            row.querySelector('input').focus();
            lucide.createIcons();
            attachValidation(row.querySelector('input'));
        }
        
        addBtn.addEventListener('click', addInput);
        
        function attachValidation(input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            });
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (this.value.length === 6) addInput();
                    else form.requestSubmit();
                }
            });
        }
        
        document.querySelectorAll('.lottery-input').forEach(attachValidation);
        
        function closeModal() {
            resultModal.classList.remove('open');
        }
        
        resultModal.addEventListener('click', function(e) {
            if (e.target === resultModal) closeModal();
        });
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const inputs = document.querySelectorAll('.lottery-input');
            const numbers = Array.from(inputs).map(i => i.value).filter(v => v.length === 6);
            
            if (numbers.length === 0) {
                alert('Please enter at least one valid 6-digit number.');
                return;
            }
            
            checkBtn.disabled = true;
            checkBtn.innerHTML = '<i data-lucide="loader-2" class="animate-spin" size="20"></i> Checking...';
            lucide.createIcons();
            
            try {
                const response = await fetch('{{ route("public.lottery-check.submit") }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    },
                    body: JSON.stringify({ 
                        numbers: numbers.join(','), 
                        draw_date: document.getElementById('drawDate').value 
                    }),
                });
                const data = await response.json();
                
                if (data.success && data.results) {
                    const wonCount = data.results.filter(r => r.won).length;
                    const totalCount = data.results.length;
                    
                    // Calculate total prize amount
                    let totalPrize = 0;
                    data.results.forEach(res => {
                        if (res.won && res.prizes) {
                            res.prizes.forEach(p => {
                                // Parse reward string (remove commas) to number
                                const amount = parseInt(p.reward.replace(/,/g, '')) || 0;
                                totalPrize += amount;
                            });
                        }
                    });
                    
                    // Summary with total prize
                    modalSummary.innerHTML = `
                        <div class="summary-box won">
                            <div class="value">${wonCount}</div>
                            <div class="label">Won</div>
                        </div>
                        <div class="summary-box lost">
                            <div class="value">${totalCount - wonCount}</div>
                            <div class="label">Not Won</div>
                        </div>
                        <div class="summary-box" style="background: rgba(245, 158, 11, 0.15); border: 1px solid var(--gold);">
                            <div class="value" style="color: var(--gold);">฿${totalPrize.toLocaleString()}</div>
                            <div class="label">Total Won</div>
                        </div>
                    `;
                    
                    // Results
                    resultList.innerHTML = data.results.map(res => {
                        let prizesHtml = '';
                        let ticketTotal = 0;
                        
                        if (res.won && res.prizes) {
                            // Calculate total for THIS ticket
                            res.prizes.forEach(p => {
                                const amount = parseInt(p.reward.replace(/,/g, '')) || 0;
                                ticketTotal += amount;
                            });
                            
                            prizesHtml = res.prizes.map(p => `${p.name}: ฿${p.reward}`).join('<br>');
                        }
                        
                        return `
                            <div class="result-item ${res.won ? 'won' : ''}">
                                <div class="result-number">${res.number}</div>
                                <div class="result-status ${res.won ? 'won' : 'lost'}">
                                    ${res.won ? '🎉 WINNER!' : 'Not Won'}
                                </div>
                                ${res.won ? `
                                    <div class="result-prizes">${prizesHtml}</div>
                                    <div style="margin-top: 0.75rem; padding-top: 0.5rem; border-top: 1px dashed rgba(255,255,255,0.15); font-weight: 700; color: var(--gold);">
                                        Total: ฿${ticketTotal.toLocaleString()}
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }).join('');
                    
                    resultModal.classList.add('open');
                    lucide.createIcons();
                } else if (data.error) {
                    alert(data.error);
                }
            } catch(e) {
                console.error(e);
            } finally {
                checkBtn.disabled = false;
                checkBtn.innerHTML = '<i data-lucide="scan-line" size="20"></i> Check Now';
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
