{{-- Thai Lottery Ticket Card Partial --}}
@php
    $ticket = $transaction->secondaryTicket;
    $statusClass = $status === 'won' ? 'won' : ($status === 'pending' ? 'pending' : '');
@endphp

<div class="lottery-ticket {{ $statusClass }}">
    <div class="ticket-top">
        <div class="ticket-logo">
            <div class="ticket-logo-icon">🎰</div>
            <div class="ticket-logo-text">
                <strong>สลากกินแบ่ง</strong>
                รัฐบาล
            </div>
        </div>
        <div class="ticket-draw-date">
            @if($ticket?->withdraw_date)
                <span>งวดวันที่</span>
                <strong>{{ $ticket->withdraw_date->format('d/m/Y') }}</strong>
            @endif
        </div>
    </div>
    
    <div class="ticket-center">
        <div class="ticket-number-label">หมายเลขสลาก</div>
        <div class="ticket-number-container">
            <div class="ticket-number">{{ $ticket?->ticket_number ?? 'N/A' }}</div>
        </div>
        
        @if($status === 'won')
            <div class="ticket-status-badge won">
                🏆 ถูกรางวัล!
            </div>
            @if($transaction->prize_won)
                <div class="prize-info">{{ $transaction->prize_won }}</div>
            @endif
        @elseif($status === 'pending')
            <div class="ticket-status-badge pending">
                ⏳ รอประกาศผล
            </div>
        @else
            <div class="ticket-status-badge not-won">
                ไม่ถูกรางวัล
            </div>
        @endif
    </div>
    
    <div class="ticket-bottom">
        <span>ซื้อเมื่อ: {{ $transaction->purchased_at->format('d/m/Y') }}</span>
        <span>฿{{ number_format($transaction->amount, 0) }}</span>
    </div>
</div>
