<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Your Lottery Tickets | Premium Result Check</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --gold: #F59E0B;
            --gold-light: #FCD34D;
            --purple: #8B5CF6;
            --blue: #3B82F6;
            --green: #10B981;
            --red: #EF4444;
            --dark: #0F172A;
            --dark-card: #1E293B;
            --dark-light: #334155;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--dark);
            color: #F8FAFC;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        
        .bg-gradient-1 {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 50%);
            animation: float1 20s ease-in-out infinite;
        }
        
        .bg-gradient-2 {
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, transparent 50%);
            animation: float2 25s ease-in-out infinite;
        }
        
        .bg-gradient-3 {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
            animation: pulse 10s ease-in-out infinite;
        }
        
        @keyframes float1 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(5deg); }
            66% { transform: translate(-20px, 20px) rotate(-5deg); }
        }
        
        @keyframes float2 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-40px, 20px) rotate(-5deg); }
            66% { transform: translate(30px, -40px) rotate(5deg); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
            50% { opacity: 0.8; transform: translate(-50%, -50%) scale(1.1); }
        }
        
        /* Floating Particles */
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(245, 158, 11, 0.3);
            border-radius: 50%;
            animation: particleFloat 15s infinite;
        }
        
        @keyframes particleFloat {
            0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
        }
        
        /* Container */
        .container {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Header */
        .header {
            text-align: center;
            padding: 3rem 1rem;
            position: relative;
        }
        
        .header-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 60px rgba(245, 158, 11, 0.3);
            animation: headerIconPulse 3s ease-in-out infinite, fadeInDown 0.8s ease-out;
        }
        
        .header-icon svg {
            width: 40px;
            height: 40px;
            color: var(--dark);
        }
        
        @keyframes headerIconPulse {
            0%, 100% { box-shadow: 0 0 60px rgba(245, 158, 11, 0.3); }
            50% { box-shadow: 0 0 80px rgba(245, 158, 11, 0.5); }
        }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .header-title {
            font-size: clamp(2rem, 6vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--gold-light) 0%, var(--gold) 50%, var(--gold-light) 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 3s linear infinite, fadeInUp 0.8s ease-out 0.2s both;
        }
        
        @keyframes shimmer {
            to { background-position: 200% center; }
        }
        
        .header-subtitle {
            font-size: 1.1rem;
            color: #94A3B8;
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }
        
        .header-subtitle strong {
            color: #F8FAFC;
        }
        
        .batch-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 100px;
            font-size: 0.9rem;
            color: #CBD5E1;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }
        
        .batch-badge svg {
            width: 16px;
            height: 16px;
            color: var(--gold);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out both;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
        .stat-card:hover {
            transform: translateY(-8px);
            border-color: rgba(245, 158, 11, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .stat-card.won {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(139, 92, 246, 0.05) 100%);
            border-color: rgba(139, 92, 246, 0.3);
        }
        
        .stat-card.pending {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.05) 100%);
            border-color: rgba(245, 158, 11, 0.2);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.blue { background: rgba(59, 130, 246, 0.15); }
        .stat-icon.purple { background: rgba(139, 92, 246, 0.15); }
        .stat-icon.yellow { background: rgba(245, 158, 11, 0.15); }
        .stat-icon.gray { background: rgba(100, 116, 139, 0.15); }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.25rem;
        }
        
        .stat-value.purple { color: var(--purple); }
        .stat-value.yellow { color: var(--gold); }
        .stat-value.gray { color: #64748B; }
        
        .stat-label {
            font-size: 0.85rem;
            color: #64748B;
            font-weight: 500;
        }
        
        /* Section */
        .section {
            margin-bottom: 3rem;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.6s ease-out both;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .section-badge {
            padding: 0.4rem 1rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .section-badge.gold {
            background: var(--gold);
            color: var(--dark);
        }
        
        .section-badge.blue {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: var(--blue);
        }
        
        .section-badge.gray {
            background: rgba(100, 116, 139, 0.15);
            color: #64748B;
        }
        
        /* Tickets Grid */
        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        
        /* Ticket Card - Winner */
        .ticket-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out both;
        }
        
        .ticket-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
        }
        
        .ticket-card.winner {
            border-color: rgba(245, 158, 11, 0.4);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(30, 41, 59, 0.9) 100%);
            animation: winnerGlow 3s ease-in-out infinite, fadeInUp 0.6s ease-out both;
        }
        
        @keyframes winnerGlow {
            0%, 100% { box-shadow: 0 0 30px rgba(245, 158, 11, 0.2); }
            50% { box-shadow: 0 0 60px rgba(245, 158, 11, 0.4); }
        }
        
        .ticket-card-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .ticket-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .ticket-icon.gold {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }
        
        .ticket-icon.blue {
            background: rgba(59, 130, 246, 0.15);
        }
        
        .ticket-icon.gray {
            background: rgba(100, 116, 139, 0.15);
        }
        
        .ticket-icon svg {
            width: 24px;
            height: 24px;
        }
        
        .ticket-status {
            padding: 0.5rem 1rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .ticket-status.winner {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            color: var(--dark);
        }
        
        .ticket-status.pending {
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: var(--gold);
            animation: pendingPulse 2s ease-in-out infinite;
        }
        
        @keyframes pendingPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .ticket-status.not-won {
            background: rgba(100, 116, 139, 0.15);
            color: #64748B;
        }
        
        .ticket-body {
            padding: 0 1.5rem 1.5rem;
            text-align: center;
        }
        
        .ticket-label {
            font-size: 0.8rem;
            color: #64748B;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .ticket-number {
            font-size: clamp(2.5rem, 8vw, 3.5rem);
            font-weight: 900;
            letter-spacing: 0.2em;
            font-family: 'Outfit', sans-serif;
            margin-bottom: 1rem;
            background: linear-gradient(180deg, #FFFFFF 0%, #94A3B8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: numberReveal 0.8s ease-out;
        }
        
        .ticket-card.winner .ticket-number {
            background: linear-gradient(180deg, var(--gold-light) 0%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 40px rgba(245, 158, 11, 0.3);
        }
        
        @keyframes numberReveal {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .ticket-footer {
            padding: 1rem 1.5rem;
            background: rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #64748B;
        }
        
        .ticket-footer span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .ticket-footer svg {
            width: 14px;
            height: 14px;
        }
        
        /* Mini Ticket Cards */
        .mini-tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .mini-ticket {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease-out both;
        }
        
        .mini-ticket:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.1);
            background: rgba(30, 41, 59, 0.7);
        }
        
        .mini-ticket-number {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            color: #94A3B8;
            margin-bottom: 0.5rem;
        }
        
        .mini-ticket-info {
            font-size: 0.75rem;
            color: #475569;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            animation: fadeIn 0.8s ease-out;
        }
        
        .empty-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .empty-text {
            color: #64748B;
            font-size: 1.1rem;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 3rem 1rem;
            color: #475569;
            font-size: 0.85rem;
        }
        
        /* Confetti Container */
        .confetti-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1000;
            overflow: hidden;
        }
        
        .confetti-piece {
            position: absolute;
            animation: confettiFall 4s ease-out forwards;
        }
        
        @keyframes confettiFall {
            0% { transform: translateY(-100%) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(1080deg); opacity: 0; }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .stat-card {
                padding: 1.25rem 1rem;
                border-radius: 16px;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
                margin-bottom: 0.75rem;
            }
            
            .stat-value {
                font-size: 1.75rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }
            
            .header {
                padding: 2rem 1rem;
            }
            
            .header-icon {
                width: 60px;
                height: 60px;
            }
            
            .header-icon svg {
                width: 28px;
                height: 28px;
            }
            
            .tickets-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .ticket-card-header {
                padding: 1.25rem;
            }
            
            .ticket-body {
                padding: 0 1.25rem 1.25rem;
            }
            
            .ticket-number {
                font-size: 2.5rem;
                letter-spacing: 0.15em;
            }
            
            .section-title {
                font-size: 1.25rem;
            }
            
            .mini-tickets-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .mini-ticket {
                padding: 1rem;
            }
            
            .mini-ticket-number {
                font-size: 1.25rem;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 0.75rem;
            }
            
            .header {
                padding: 1.5rem 0.75rem;
            }
            
            .batch-badge {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .ticket-card {
                border-radius: 18px;
            }
            
            .ticket-icon {
                width: 44px;
                height: 44px;
            }
            
            .ticket-number {
                font-size: 2rem;
            }
            
            .ticket-footer {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    {{-- Animated Background --}}
    <div class="bg-animation">
        <div class="bg-gradient-1"></div>
        <div class="bg-gradient-2"></div>
        <div class="bg-gradient-3"></div>
        @for($i = 0; $i < 20; $i++)
            <div class="particle" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 15) }}s; animation-duration: {{ rand(15, 25) }}s;"></div>
        @endfor
    </div>
    
    {{-- Confetti for Winners --}}
    @php
        $wonTickets = $transactions->where('status', 'won')->count();
        $pendingTickets = $transactions->where('status', 'pending')->count();
        $notWonTickets = $transactions->where('status', 'not_won')->count();
        $totalTickets = $transactions->count();
    @endphp
    
    @if($wonTickets > 0)
        <div class="confetti-container" id="confetti"></div>
    @endif

    <div class="container">
        {{-- Header --}}
        <header class="header">
            <div class="header-icon">
                <i data-lucide="crown"></i>
            </div>
            <h1 class="header-title">Your Lottery Tickets</h1>
            <p class="header-subtitle">
                Welcome back, <strong>{{ $transactions->first()->customer_display_name ?? 'Valued Customer' }}</strong>
            </p>
            
            @if($transactions->first()->secondaryTicket?->batch_number)
                <div class="batch-badge">
                    <i data-lucide="layers"></i>
                    Batch #{{ $transactions->first()->secondaryTicket->batch_number }}
                </div>
            @endif
        </header>
        
        {{-- Statistics --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">🎫</div>
                <div class="stat-value">{{ $totalTickets }}</div>
                <div class="stat-label">Total Tickets</div>
            </div>
            <div class="stat-card won">
                <div class="stat-icon purple">🏆</div>
                <div class="stat-value purple">{{ $wonTickets }}</div>
                <div class="stat-label">Won</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon yellow">⏳</div>
                <div class="stat-value yellow">{{ $pendingTickets }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon gray">📋</div>
                <div class="stat-value gray">{{ $notWonTickets }}</div>
                <div class="stat-label">Not Won</div>
            </div>
        </div>
        
        {{-- Winning Tickets --}}
        @if($wonTickets > 0)
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">🎉 Winning Tickets</h2>
                    <span class="section-badge gold">{{ $wonTickets }} {{ $wonTickets === 1 ? 'Win' : 'Wins' }}</span>
                </div>
                <div class="tickets-grid">
                    @foreach($transactions->where('status', 'won') as $index => $transaction)
                        <div class="ticket-card winner" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="ticket-card-header">
                                <div class="ticket-icon gold">
                                    <i data-lucide="party-popper" style="color: #0F172A;"></i>
                                </div>
                                <span class="ticket-status winner">{{ $transaction->prize_won ?? 'Winner!' }}</span>
                            </div>
                            <div class="ticket-body">
                                <p class="ticket-label">Ticket Number</p>
                                <div class="ticket-number">{{ $transaction->secondaryTicket->ticket_number }}</div>
                            </div>
                            <div class="ticket-footer">
                                <span><i data-lucide="layers"></i> Batch #{{ $transaction->secondaryTicket->batch_number ?? '-' }}</span>
                                <span><i data-lucide="calendar"></i> {{ $transaction->drawResult?->date_en ?? 'Confirmed' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        {{-- Pending Tickets --}}
        @if($pendingTickets > 0)
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title">⏳ Pending Verification</h2>
                    <span class="section-badge blue">{{ $pendingTickets }} {{ $pendingTickets === 1 ? 'Ticket' : 'Tickets' }}</span>
                </div>
                <div class="tickets-grid">
                    @foreach($transactions->where('status', 'pending') as $index => $transaction)
                        <div class="ticket-card" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="ticket-card-header">
                                <div class="ticket-icon blue">
                                    <i data-lucide="ticket" style="color: #3B82F6;"></i>
                                </div>
                                <span class="ticket-status pending">Pending</span>
                            </div>
                            <div class="ticket-body">
                                <p class="ticket-label">Ticket Number</p>
                                <div class="ticket-number">{{ $transaction->secondaryTicket->ticket_number }}</div>
                            </div>
                            <div class="ticket-footer">
                                <span><i data-lucide="calendar"></i> 
                                    @if($transaction->secondaryTicket->withdraw_date)
                                        Draw: {{ $transaction->secondaryTicket->withdraw_date->format('M d, Y') }}
                                    @else
                                        Awaiting Draw
                                    @endif
                                </span>
                                <span style="color: #3B82F6;">Check Soon →</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        {{-- Not Won Tickets --}}
        @if($notWonTickets > 0)
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title" style="color: #64748B;">Past Results</h2>
                    <span class="section-badge gray">{{ $notWonTickets }} Not Won</span>
                </div>
                <div class="mini-tickets-grid">
                    @foreach($transactions->where('status', 'not_won') as $index => $transaction)
                        <div class="mini-ticket" style="animation-delay: {{ $index * 0.05 }}s">
                            <div class="mini-ticket-number">{{ $transaction->secondaryTicket->ticket_number }}</div>
                            <div class="mini-ticket-info">
                                {{ $transaction->secondaryTicket->withdraw_date ? $transaction->secondaryTicket->withdraw_date->format('M d, Y') : '-' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        {{-- Empty State --}}
        @if($totalTickets === 0)
            <div class="empty-state">
                <div class="empty-icon">🎫</div>
                <p class="empty-text">No tickets found for this batch</p>
            </div>
        @endif
    </div>
    
    <footer class="footer">
        <p>© {{ date('Y') }} Lottery Service • Best of Luck! 🍀</p>
    </footer>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
        
        // Confetti Animation for Winners
        @if($wonTickets > 0)
        (function() {
            const container = document.getElementById('confetti');
            const colors = ['#F59E0B', '#FCD34D', '#8B5CF6', '#3B82F6', '#10B981', '#EF4444', '#EC4899'];
            const emojis = ['🎉', '🎊', '✨', '🌟', '💫', '🏆', '👑'];
            
            function createConfetti() {
                for (let i = 0; i < 80; i++) {
                    setTimeout(() => {
                        const confetti = document.createElement('div');
                        confetti.className = 'confetti-piece';
                        confetti.style.left = Math.random() * 100 + '%';
                        confetti.style.animationDuration = (3 + Math.random() * 2) + 's';
                        confetti.style.animationDelay = Math.random() * 0.5 + 's';
                        
                        if (Math.random() > 0.6) {
                            confetti.textContent = emojis[Math.floor(Math.random() * emojis.length)];
                            confetti.style.fontSize = (1.5 + Math.random()) + 'rem';
                        } else {
                            confetti.style.width = (8 + Math.random() * 8) + 'px';
                            confetti.style.height = confetti.style.width;
                            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                            confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
                        }
                        
                        container.appendChild(confetti);
                        setTimeout(() => confetti.remove(), 5000);
                    }, i * 40);
                }
            }
            
            createConfetti();
            // Second wave
            setTimeout(createConfetti, 2000);
        })();
        @endif
    </script>
</body>
</html>
