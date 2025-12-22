@extends('public.layout')

@section('title', 'Lottery Results - ' . ($drawResult->date_en ?? $drawResult->draw_date->format('d M Y')))

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    {{-- Back Button --}}
    <div class="mb-6">
        <a href="{{ route('lottery-check') }}" class="inline-flex items-center gap-2 text-gold hover:text-gold-dark transition" style="color: #FFD700; text-decoration: none; font-weight: 600;">
            <svg class="w-5 h-5" style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Check Lottery
        </a>
    </div>

    {{-- Draw Info Header --}}
    <div class="mb-8 text-center" style="background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%); border-radius: 1rem; padding: 2rem; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <h1 style="font-size: 2.25rem; font-weight: 800; color: #0F172A; margin-bottom: 0.5rem;">
            Thai Government Lottery Results
        </h1>
        <p style="font-size: 1.25rem; color: rgba(15, 23, 42, 0.8);">
            Draw Date: {{ $drawResult->date_en ?? $drawResult->draw_date->format('d M Y') }}
        </p>
    </div>

    {{-- Prizes Display --}}
    @if($drawResult->normalized_prizes)
        <div style="display: grid; gap: 1.5rem;">
            @foreach($drawResult->normalized_prizes as $prize)
                <div style="background: #1E293B; border-radius: 1rem; padding: 1.5rem; border: 1px solid rgba(255,215,0,0.2); transition: border-color 0.3s;">
                    <div style="display: flex; align-items: center; justify-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                        <h3 style="font-size: 1.25rem; font-weight: 600; color: #FFD700; margin: 0;">{{ $prize['name'] ?? 'Prize' }}</h3>
                        @if(isset($prize['reward']))
                            <span style="font-size: 1rem; color: rgba(255,255,255,0.8);">{{ $prize['reward'] }}</span>
                        @endif
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                        @foreach($prize['numbers'] ?? [] as $number)
                            <div style="background: linear-gradient(135deg, rgba(255,215,0,0.2) 0%, rgba(255,215,0,0.1) 100%); padding: 0.75rem 1.5rem; border-radius: 0.5rem;">
                                <span style="font-family: 'Courier New', monospace; font-size: 1.5rem; font-weight: 700; color: #FFD700; letter-spacing: 0.2em;">
                                    {{ $number }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Running Numbers --}}
    @if($drawResult->running_numbers)
        <div style="margin-top: 2rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #FFD700; margin-bottom: 1rem;">Last Digit Prizes</h2>
            <div style="display: grid; gap: 1rem;">
                @foreach($drawResult->running_numbers as $running)
                    <div style="background: #1E293B; border-radius: 1rem; padding: 1.5rem; border: 1px solid rgba(255,215,0,0.2);">
                        <h3 style="font-size: 1rem; font-weight: 600; color: white; margin-bottom: 0.75rem;">
                            {{ $running['name'] ?? 'Last Digits' }}
                            @if(isset($running['reward']))
                                <span style="font-size: 0.875rem; color: rgba(255,255,255,0.6); margin-left: 0.5rem;">({{ $running['reward'] }})</span>
                            @endif
                        </h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                            @foreach($running['numbers'] ?? [] as $number)
                                <div style="background: linear-gradient(135deg, rgba(139,92,246,0.2) 0%, rgba(139,92,246,0.1) 100%); padding: 0.5rem 1.25rem; border-radius: 0.5rem;">
                                    <span style="font-family: 'Courier New', monospace; font-size: 1.25rem; font-weight: 700; color: #8B5CF6; letter-spacing: 0.15em;">
                                        {{ $number }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Footer --}}
    <div style="margin-top: 3rem; text-align: center; color: rgba(255,255,255,0.5); font-size: 0.875rem;">
        <p>Source: Government Lottery Office</p>
        <p style="margin-top: 0.25rem;">© {{ date('Y') }} Kan-San Lottery</p>
    </div>
</div>

<style>
    :root {
        --gold: #FFD700;
        --gold-dark: #B8860B;
        --purple: #8B5CF6;
        --bg-dark: #0F172A;
        --bg-card: #1E293B;
    }
    
    body {
        background: linear-gradient(135deg, #0F172A 0%, #1E3A5F 50%, #0F172A 100%);
        min-height: 100vh;
        color: white;
    }
</style>
@endsection

