<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lottery Result Check</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700;800&family=Courier+Prime:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-premium: 0 20px 40px -5px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            min-height: 100vh;
            background: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.3) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(236, 72, 153, 0.3) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.3) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(59, 130, 246, 0.3) 0px, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow-x: hidden;
            flex-direction: column;
            color: white;
        }

        /* Floating background particles */
        .bg-particle {
            position: absolute;
            border-radius: 50%;
            background: white;
            opacity: 0.1;
            z-index: 0;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-100vh) rotate(360deg); }
        }

        .container {
            width: 100%;
            max-width: 600px;
            position: relative;
            z-index: 10;
            animation: slideUpFade 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        /* Glass Backdrop Container */
        .glass-frame {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .page-title {
            text-align: center;
            margin-bottom: -1rem;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #bfdbfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        /* The Ticket */
        .ticket-wrapper {
            width: 100%;
            transition: transform 0.3s ease;
            filter: drop-shadow(0 25px 25px rgba(0,0,0,0.25));
        }

        .ticket-wrapper:hover {
            transform: translateY(-5px) scale(1.02);
        }

        .real-ticket {
            background-color: white;
            background-image: 
                radial-gradient(circle at 100% 0%, rgba(200,160,255,0.1) 0%, transparent 20%),
                radial-gradient(circle at 0% 100%, rgba(200,160,255,0.1) 0%, transparent 20%),
                repeating-linear-gradient(-45deg, transparent, transparent 5px, rgba(0,0,0,0.02) 5px, rgba(0,0,0,0.02) 10px);
            border-radius: 16px;
            display: flex;
            position: relative;
            overflow: hidden;
            color: #1e293b;
        }

        /* Wavy Cut effect CSS (Optional or simplified) */
        .real-ticket::before, .real-ticket::after {
            content: '';
            position: absolute;
            height: 20px;
            width: 20px;
            background: #0f172a; /* Match body bg */
            border-radius: 50%;
            z-index: 5;
        }
        .real-ticket::before { top: 50%; left: -10px; transform: translateY(-50%); }
        .real-ticket::after { top: 50%; right: -10px; transform: translateY(-50%); }

        /* Left Section */
        .ticket-left {
            width: 30%;
            background: #f1f5f9;
            border-right: 2px dashed #cbd5e1;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            position: relative;
        }

        .logo-box {
            width: 56px;
            height: 56px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            color: #3b82f6;
        }

        .price-display {
            text-align: center;
            margin-top: 0.5rem;
        }

        .price-val {
            font-size: 2.25rem;
            font-weight: 800;
            color: #db2777;
            line-height: 1;
            text-shadow: 1px 1px 0px rgba(0,0,0,0.05);
        }

        .price-unit {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 1px;
        }

        /* Right Section */
        .ticket-right {
            width: 70%;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: white;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.7rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .ticket-body {
            text-align: center;
            margin: 1rem 0;
        }
        
        .main-digits {
            font-family: 'Courier Prime', monospace;
            font-size: 3.5rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -1px;
            line-height: 1;
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .draw-date {
            color: #3b82f6;
            font-weight: 700;
            font-size: 1rem;
            margin-top: 0.5rem;
            text-transform: uppercase;
        }

        .ticket-footer {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-pill {
            background: #fdf2f8;
            border: 1px solid #fce7f3;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #be185d;
            flex: 1;
            text-align: center;
        }

        /* Stamps */
        .stamp-status {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            z-index: 20;
            font-size: 2.5rem;
            font-weight: 900;
            padding: 0.5rem 1.5rem;
            border: 5px solid;
            border-radius: 12px;
            text-transform: uppercase;
            mix-blend-mode: multiply;
            opacity: 0;
            animation: stampBounce 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards 0.5s;
        }

        .won { color: #16a34a; border-color: #16a34a; }
        .lost { color: #dc2626; border-color: #dc2626; }
        .pending { color: #d97706; border-color: #d97706; }

        @keyframes stampBounce {
            0% { transform: translate(-50%, -50%) scale(3) rotate(-15deg); opacity: 0; }
            100% { transform: translate(-50%, -50%) scale(1) rotate(-15deg); opacity: 0.85; }
        }

        /* Beautiful Button */
        .check-again-btn {
            background: linear-gradient(135deg, #FFD700 0%, #F59E0B 100%);
            color: #78350f;
            border: none;
            padding: 1rem 3rem;
            border-radius: 99px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1rem;
        }

        .check-again-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.6);
            filter: brightness(110%);
        }

        .check-again-btn:active {
            transform: translateY(0);
        }

        /* Animations */
        @keyframes slideUpFade {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Mobile Improvements */
        @media (max-width: 640px) {
            body {
                padding: 0.5rem;
                justify-content: flex-start; /* prevent centering cutoff on small screens */
            }
            .container {
                max-width: 100%;
                margin-top: 1rem;
            }
            .glass-frame {
                padding: 1.25rem;
                border-radius: 16px;
                gap: 1.5rem;
            }
            .real-ticket { 
                flex-direction: column; 
            }
            .ticket-left { 
                width: 100%; 
                flex-direction: row; 
                justify-content: space-between; 
                padding: 1rem;
                border-right: none;
                border-bottom: 2px dashed #cbd5e1;
                background: #f8fafc;
            }
            .ticket-right { 
                width: 100%; 
                padding: 1.25rem 1rem; 
                gap: 1rem;
            }
            .price-display { margin-top: 0; text-align: right; }
            .price-val { font-size: 2rem; }
            .logo-box { width: 48px; height: 48px; }
            
            .ticket-header {
                font-size: 0.65rem;
            }
            .main-digits { 
                font-size: 2.8rem; /* Prevent overflow */
                letter-spacing: -1px;
            }
            .real-ticket::before { top: auto; bottom: -10px; left: 20%; transform: none; }
            .real-ticket::after { top: auto; bottom: -10px; right: 20%; transform: none; }
            
            .stamp-status {
                font-size: 2rem;
                border-width: 4px;
                padding: 0.5rem 1rem;
            }
            .check-again-btn {
                width: 100%;
                justify-content: center;
                padding: 0.8rem;
            }
        }
    </style>
</head>
<body>

    <!-- Background Elements -->
    @for($i = 0; $i < 10; $i++)
        <div class="bg-particle" style="
            left: {{ rand(0, 100) }}%; 
            bottom: -50px;
            width: {{ rand(5, 20) }}px; 
            height: {{ rand(5, 20) }}px; 
            animation-duration: {{ rand(15, 30) }}s; 
            animation-delay: {{ rand(0, 10) }}s;">
        </div>
    @endfor

    @if($transaction->status === 'won')
        <canvas id="confetti-canvas"></canvas>
    @endif

    <div class="container">
        
        <div class="page-title">
            <h1>Lottery Result</h1>
            <p>Official Digital Verification</p>
        </div>

        <div class="glass-frame">
            
            <div class="ticket-wrapper">
                <div class="real-ticket">
                    <!-- Status Stamp -->
                    @if($transaction->status === 'won')
                        <div class="stamp-status won">WINNER</div>
                    @elseif($transaction->status === 'not_won')
                         <div class="stamp-status lost">NOT WON</div>
                    @else
                         <div class="stamp-status pending">PENDING</div>
                    @endif

                    <div class="ticket-left">
                        <div class="logo-box">
                            <i data-lucide="crown" size="28"></i>
                        </div>
                        <div class="price-display">
                            <div class="price-val">100</div>
                            <div class="price-unit">BAHT</div>
                        </div>
                    </div>

                    <div class="ticket-right">
                        <div class="ticket-header">
                            <span>Thai Gov Lottery</span>
                            <span>#{{ substr($transaction->public_token, 0, 8) }}</span>
                        </div>

                        <!-- Customer Name Section -->
                        @if($transaction->customer)
                        <div style="text-align:center; margin-top: 0.5rem; font-size: 0.85rem; font-weight: 600; color: #64748b;">
                            Owner: <span style="color: #334155;">{{ $transaction->customer->customer_name }}</span>
                        </div>
                        @endif

                        <div class="ticket-body">
                            <div class="main-digits">
                                {{ $ticket?->ticket_number ?? '000000' }}
                            </div>
                            <div class="draw-date">
                                {{ $ticket?->withdraw_date ? $ticket->withdraw_date->format('d F Y') : 'Waiting Date' }}
                            </div>
                        </div>

                        <div class="ticket-footer">
                            <div class="info-pill" style="width: 100%; text-align: center;">
                                Period: {{ $ticket?->batch_number ?? '01' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button onclick="window.location.reload()" class="check-again-btn">
                <i data-lucide="rotate-cw" size="20"></i>
                Check Another Ticket
            </button>

        </div>
    </div>

    <script>
        lucide.createIcons();

        @if($transaction->status === 'won')
        (function() {
            const canvas = document.getElementById('confetti-canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            const pieces = [];
            const numberOfPieces = 200;
            const colors = ['#FCD34D', '#F472B6', '#34D399', '#60A5FA'];

            function newPiece() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height - canvas.height;
                this.rotation = Math.random() * 360;
                this.color = colors[Math.floor(Math.random() * colors.length)];
                this.size = Math.random() * 8 + 4;
                this.speed = Math.random() * 4 + 2;
                this.oscillationSpeed = Math.random() * 0.05 + 0.01;
                
                this.update = function() {
                    this.y += this.speed;
                    this.rotation += this.speed;
                    this.x += Math.sin(this.y * this.oscillationSpeed);
                    
                    if (this.y > canvas.height) {
                        this.y = -20;
                        this.x = Math.random() * canvas.width;
                    }
                }
                
                this.draw = function() {
                    ctx.fillStyle = this.color;
                    ctx.save();
                    ctx.translate(this.x, this.y);
                    ctx.rotate(this.rotation * Math.PI / 180);
                    ctx.fillRect(-this.size / 2, -this.size / 2, this.size, this.size);
                    ctx.restore();
                }
            }

            for (let i = 0; i < numberOfPieces; i++) { pieces.push(new newPiece()); }

            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                pieces.forEach(p => { p.update(); p.draw(); });
                requestAnimationFrame(animate);
            }
            animate();
            window.addEventListener('resize', () => { canvas.width = window.innerWidth; canvas.height = window.innerHeight; });
        })();
        @endif
    </script>
</body>
</html>
