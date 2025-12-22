<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลหวยย้อนหลัง | Lottery History</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #FFD700;
            --gold-dark: #B8860B;
            --red: #C41E3A;
            --bg-dark: #0F172A;
            --bg-card: #1E293B;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Prompt', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0F172A 0%, #1E3A5F 50%, #0F172A 100%);
            color: white;
        }
        
        .header {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
            padding: 1.5rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 1.8rem;
            color: var(--bg-dark);
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .year-filter {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            justify-content: center;
        }
        
        .year-btn {
            padding: 0.75rem 1.5rem;
            background: var(--bg-card);
            border: 2px solid rgba(255,215,0,0.3);
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .year-btn:hover, .year-btn.active {
            background: var(--gold);
            color: var(--bg-dark);
            border-color: var(--gold);
        }
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .result-card {
            background: var(--bg-card);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s;
        }
        
        .result-card:hover {
            border-color: var(--gold);
            transform: translateY(-3px);
        }
        
        .result-header {
            background: linear-gradient(135deg, var(--red) 0%, #8B0000 100%);
            padding: 1rem;
            text-align: center;
        }
        
        .result-date {
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .result-body {
            padding: 1.5rem;
        }
        
        .prize-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .prize-row:last-child {
            border-bottom: none;
        }
        
        .prize-name {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .prize-number {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: var(--gold);
            letter-spacing: 0.1em;
        }
        
        .view-detail-btn {
            display: block;
            text-align: center;
            padding: 0.75rem;
            background: rgba(255,215,0,0.1);
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .view-detail-btn:hover {
            background: var(--gold);
            color: var(--bg-dark);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gold);
            text-decoration: none;
            margin-bottom: 2rem;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .footer {
            text-align: center;
            padding: 2rem;
            font-size: 0.8rem;
            opacity: 0.6;
        }
        
        .footer a { color: var(--gold); text-decoration: none; }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            opacity: 0.6;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        /* Responsive - Mobile First */
        @media (max-width: 480px) {
            .header { padding: 1rem; }
            .header h1 { font-size: 1.3rem; }
            
            .container { padding: 1rem 0.75rem; }
            
            .back-link { 
                margin-bottom: 1rem;
                font-size: 0.9rem;
            }
            
            .year-filter {
                gap: 0.4rem;
                margin-bottom: 1.5rem;
            }
            
            .year-btn {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
            
            .results-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .result-header { padding: 0.75rem; }
            .result-date { font-size: 1rem; }
            .result-body { padding: 1rem; }
            
            .prize-row { padding: 0.5rem 0; }
            .prize-name { font-size: 0.8rem; }
            .prize-number { font-size: 0.9rem; }
            
            .view-detail-btn { padding: 0.6rem; font-size: 0.85rem; }
            
            .empty-state { padding: 2rem 1rem; }
            .empty-state-icon { font-size: 3rem; }
        }
        
        @media (min-width: 481px) and (max-width: 768px) {
            .header h1 { font-size: 1.5rem; }
            
            .results-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }
        
        @media (min-width: 769px) {
            .results-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
        
        /* Touch-friendly */
        @media (hover: none) and (pointer: coarse) {
            .year-btn { min-height: 44px; }
            .view-detail-btn { min-height: 48px; display: flex; align-items: center; justify-content: center; }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>📜 ผลสลากย้อนหลัง</h1>
    </header>

    <div class="container">
        <a href="{{ route('public.lottery-check') }}" class="back-link">
            ← กลับไปตรวจหวย
        </a>

        <div class="year-filter">
            @foreach($years as $year)
                <a href="{{ route('public.lottery-history', ['year' => $year]) }}" 
                   class="year-btn {{ $selectedYear == $year ? 'active' : '' }}">
                    {{ $year + 543 }}
                </a>
            @endforeach
        </div>

        @if($results->count() > 0)
            <div class="results-grid">
                @foreach($results as $result)
                    <div class="result-card">
                        <div class="result-header">
                            <div class="result-date">
                                {{ $result->date_th ?? $result->date_en ?? $result->draw_date->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="result-body">
                            @php
                                $prizes = $result->normalized_prizes;
                                $firstPrize = collect($prizes)->firstWhere('id', 'first') 
                                           ?? collect($prizes)->firstWhere('name', 'รางวัลที่ 1')
                                           ?? collect($prizes)->first();
                            @endphp
                            
                            @if($firstPrize)
                                <div class="prize-row">
                                    <span class="prize-name">รางวัลที่ 1</span>
                                    <span class="prize-number">
                                        {{ is_array($firstPrize['numbers'] ?? null) ? ($firstPrize['numbers'][0] ?? '-') : '-' }}
                                    </span>
                                </div>
                            @endif
                            
                            @php
                                $last2 = collect($result->running_numbers ?? [])->firstWhere('id', 'runningNumberBackTwo');
                                $last3 = collect($result->running_numbers ?? [])->firstWhere('id', 'runningNumberFrontThree');
                            @endphp
                            
                            @if($last2)
                                <div class="prize-row">
                                    <span class="prize-name">เลขท้าย 2 ตัว</span>
                                    <span class="prize-number">{{ implode(', ', $last2['numbers'] ?? []) }}</span>
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('public.lottery-result', $result->draw_date->format('Y-m-d')) }}" class="view-detail-btn">
                            ดูรายละเอียดทั้งหมด →
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>ไม่พบผลสลากในปี {{ $selectedYear + 543 }}</p>
            </div>
        @endif
    </div>

    <footer class="footer">
        <p>© {{ date('Y') }} Thai Lottery | <a href="{{ route('public.lottery-check') }}">ตรวจหวยออนไลน์</a></p>
    </footer>
</body>
</html>
