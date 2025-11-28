@extends('layouts.vertical', ['title' => 'Cpanel Performance'])

@section('css')
@endsection

@section('content')
@include('layouts.partials/page-title', ['subtitle' => 'Dashboards', 'title' => 'Cpanel Performance'])

<div class="mb-5 flex items-center justify-between">
    <p class="text-default-600">Monitor your hosting resource usage and server statistics</p>
    <button
        id="refresh-btn"
        class="btn bg-primary text-white hover:bg-primary-600"
        type="button"
    >
        <svg class="w-4 h-4 mr-2 animate-spin hidden" id="refresh-spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        <i class="size-4 mr-2" id="refresh-icon" data-lucide="refresh-cw"></i>
        Refresh
    </button>
</div>

{{-- Resource Usage Cards --}}
<div id="stats-cards" class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-5 mb-5">
    @foreach($stats as $stat)
    @php
        $colorMap = [
            'Bandwidth Usage' => 'success',
            'Disk Usage' => 'danger',
            'Email Accounts' => 'secondary',
            'FTP Accounts' => 'info',
            'SQL Databases' => 'warning',
            'Subdomains' => 'primary',
            'Addon Domains' => 'info',
            'Parked Domains' => 'secondary',
        ];
        $iconMap = [
            'Bandwidth Usage' => 'activity',
            'Disk Usage' => 'database',
            'Email Accounts' => 'mail',
            'FTP Accounts' => 'folder',
            'SQL Databases' => 'layout-list',
            'Subdomains' => 'network',
            'Addon Domains' => 'globe',
            'Parked Domains' => 'parking-circle-off',
        ];
        $color = $colorMap[$stat['name']] ?? 'secondary';
        $icon = $iconMap[$stat['name']] ?? 'bar-chart-2';
    @endphp
    <div class="card bg-{{$color}}/15 overflow-hidden stat-card" data-stat="{{ $stat['name'] }}">
        <div class="card-body">
            <i class="absolute top-0 size-32 text-{{$color}}/10 -end-10" data-lucide="{{$icon}}"></i>
            <div class="btn btn-icon size-12 bg-{{$color}}">
                <i class="size-6 text-white" data-lucide="{{$icon}}"></i>
            </div>
            @php
                // Check if current value already contains unit (e.g., "3.22 GB")
                $currentStr = (string)$stat['current'];
                $hasUnitInCurrent = preg_match('/[A-Za-z]/', $currentStr);
            @endphp
            <h5 class="mt-5 mb-2 text-lg font-semibold">
                <span class="counter-value" data-target="{{$stat['current']}}">{{$stat['current']}}</span>
                @if($stat['max'] && $stat['max'] != 'Unlimited')
                    <span class="text-xs text-default-600"> / {{$stat['max']}}</span>
                @endif
                @if($stat['unit'] && !$hasUnitInCurrent) 
                    <span class="text-sm"> {{$stat['unit']}}</span>
                @endif
            </h5>
            <p class="text-sm text-default-700">{{$stat['name']}}</p>
            <div class="w-full bg-default-200 rounded-full h-2 mt-3">
                <div class="bg-{{$color}} h-2 rounded-full transition-all stat-progress"
                     style="width: {{$stat['percent'] ?? 0}}%"></div>
            </div>
            <p class="text-xs text-default-600 mt-1">{{$stat['percent'] ?? 0}}% used</p>
            @if($stat['percent'] > 80)
                <div class="mt-2 py-0.5 px-2.5 inline-flex items-center gap-x-1 text-xs font-medium bg-danger/15 text-danger rounded">
                    <i class="size-3" data-lucide="alert-triangle"></i>
                    High Usage
                </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- SYSTEM DETAILS --}}
<div class="grid md:grid-cols-2 gap-5 mb-5">

    {{-- CPU Usage --}}
    @if(isset($cpu['data'][0]))
    @php
        $cpuData = $cpu['data'][0];
        $total = $cpuData['cpu_limit'] ?? 100;
        $used = $cpuData['cpu_usage'] ?? 0;
        $percent = ($used && $total) ? ($used / $total) * 100 : 0;
    @endphp
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">CPU Usage</h6>
        </div>
        <div class="card-body">
            <div class="flex items-center gap-3 mb-4">
                <div class="btn btn-icon size-12 bg-primary">
                    <i class="size-6 text-white" data-lucide="cpu"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold">{{ number_format($used, 1) }} / {{ $total }}</h3>
                    <p class="text-sm text-default-600">{{ number_format($percent) }}% utilized</p>
                </div>
            </div>
            <div class="w-full bg-default-200 rounded-full h-3">
                <div class="bg-primary h-3 rounded-full transition-all"
                    style="width: {{$percent}}%"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Process List --}}
    @if(isset($procs['data']) && is_array($procs['data']))
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Top Processes</h6>
        </div>
        <div class="overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-default-200">
                        <thead class="bg-default-150">
                            <tr class="text-default-600">
                                <th class="px-3.5 py-3 text-start text-sm font-medium">PID</th>
                                <th class="px-3.5 py-3 text-start text-sm font-medium">User</th>
                                <th class="px-3.5 py-3 text-start text-sm font-medium">CPU</th>
                                <th class="px-3.5 py-3 text-start text-sm font-medium">Command</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-default-200">
                            @foreach(array_slice($procs['data'], 0, 5) as $proc)
                                <tr class="text-default-800">
                                    <td class="px-3.5 py-2.5 whitespace-nowrap text-sm">{{ $proc['pid'] ?? '-' }}</td>
                                    <td class="px-3.5 py-2.5 whitespace-nowrap text-sm">{{ $proc['user'] ?? '-' }}</td>
                                    <td class="px-3.5 py-2.5 whitespace-nowrap text-sm font-semibold">{{ $proc['cpu'] ?? '-' }}%</td>
                                    <td class="px-3.5 py-2.5 text-sm truncate max-w-xs" title="@if(isset($proc['command']) && is_array($proc['command'])){{ implode(', ', $proc['command']) }}@else{{ $proc['command'] ?? '-' }}@endif">
                                        @if(isset($proc['command']) && is_array($proc['command']))
                                            {{ implode(', ', $proc['command']) }}
                                        @else
                                            {{ \Illuminate\Support\Str::limit($proc['command'] ?? '-', 40) }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Cron Jobs --}}
    @if(isset($cron['data']) && is_array($cron['data']))
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Cron Jobs</h6>
        </div>
        <div class="card-body">
            <ul class="flex flex-col gap-3">
            @forelse($cron['data'] as $job)
                <li class="flex items-start gap-3 text-sm">
                    <div class="bg-warning/10 btn size-8">
                        <i class="text-warning size-4" data-lucide="clock"></i>
                    </div>
                    <div class="grow">
                        <h6 class="text-default-900 font-semibold">
                            @if(isset($job['command']) && is_array($job['command']))
                                {{ implode(', ', $job['command']) }}
                            @else
                                {{ $job['command'] ?? '-' }}
                            @endif
                        </h6>
                        <p class="text-default-600 text-xs">
                            {{ $job['minute'] ?? '-' }} {{ $job['hour'] ?? '-' }} {{ $job['day'] ?? '-' }} {{ $job['month'] ?? '-' }} {{ $job['weekday'] ?? '-' }}
                        </p>
                    </div>
                </li>
            @empty
                <li class="text-default-500 text-center py-4">No cron jobs configured</li>
            @endforelse
            </ul>
        </div>
    </div>
    @endif

    {{-- Domain Aliases --}}
    @if(isset($aliases['data']) && is_array($aliases['data']))
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Domain Aliases</h6>
        </div>
        <div class="card-body">
            <ul class="flex flex-col gap-2">
                @foreach($aliases['data'] as $alias)
                    <li class="flex items-center gap-3 text-sm">
                        <div class="bg-success/10 btn size-8">
                            <i class="text-success size-4" data-lucide="globe"></i>
                        </div>
                        <h6 class="text-default-900">
                            @if(isset($alias['domain']) && is_array($alias['domain']))
                                {{ implode(', ', $alias['domain']) }}
                            @else
                                {{ $alias['domain'] ?? '-' }}
                            @endif
                        </h6>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- PHP Versions --}}
    @if(isset($php['data']) && is_array($php['data']))
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">PHP Versions</h6>
        </div>
        <div class="card-body">
            <ul class="flex flex-col gap-2">
                @foreach($php['data'] as $ver)
                    <li class="flex items-center gap-3 text-sm">
                        <div class="bg-secondary/10 btn size-8">
                            <i class="text-secondary size-4" data-lucide="code"></i>
                        </div>
                        <h6 class="text-default-900">
                            @if(is_array($ver))
                                {{ implode(', ', $ver) }}
                            @else
                                {{ $ver['version'] ?? $ver }}
                            @endif
                        </h6>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Server Connections --}}
    @if(isset($connections['data']))
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Server Connections</h6>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-default-600 mb-1">Current</p>
                    <h3 class="text-2xl font-bold text-default-900">{{ $connections['data']['connections']['current'] ?? '-' }}</h3>
                </div>
                <div>
                    <p class="text-sm text-default-600 mb-1">Maximum</p>
                    <h3 class="text-2xl font-bold text-default-900">{{ $connections['data']['connections']['max'] ?? '-' }}</h3>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Email Disk Usage --}}
    @if(isset($emailDisk['data']) && is_array($emailDisk['data']))
    <div class="card">
        <div class="card-header">
            <h6 class="card-title">Email Disk Usage</h6>
        </div>
        <div class="card-body">
            <ul class="flex flex-col gap-3">
                @foreach($emailDisk['data'] as $mail)
                    <li class="flex items-center gap-3 text-sm">
                        <div class="bg-danger/10 btn size-8">
                            <i class="text-danger size-4" data-lucide="mail"></i>
                        </div>
                        <h6 class="grow text-default-900">
                            @if(isset($mail['mailbox']) && is_array($mail['mailbox']))
                                {{ implode(', ', $mail['mailbox']) }}
                            @else
                                {{ $mail['mailbox'] ?? '-' }}
                            @endif
                        </h6>
                        <p class="text-default-600">{{ $mail['diskused'] ?? '-' }} {{ $mail['units'] ?? '' }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
    // Counter animation for statistics
    function animateCounters() {
        document.querySelectorAll('.counter-value').forEach((el) => {
            const target = el.getAttribute('data-target');
            if (!target) return;
            let count = 0, end = parseInt(target.replace(/[^\d]/g,'')), increment = Math.ceil(end / 30);
            function update() {
                count += increment;
                el.innerText = (count < end) ? count : end;
                if(count < end) requestAnimationFrame(update);
            }
            update();
        });
    }
    animateCounters();

    // Refresh button
    document.getElementById('refresh-btn').addEventListener('click', async function() {
        const btn = this;
        const spinner = document.getElementById('refresh-spinner');
        const icon = document.getElementById('refresh-icon');
        
        spinner.classList.remove('hidden');
        icon.classList.add('hidden');
        btn.setAttribute('disabled', true);

        try {
            const resp = await fetch("{{ route('cpanel.stats.refresh') }}", {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const html = await resp.text();
            document.getElementById('stats-cards').innerHTML = html;
            
            // Re-initialize Lucide icons
            if(typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            animateCounters();
        } catch (err) {
            console.error('Refresh error:', err);
            alert('Failed to refresh stats. Please try again.');
        } finally {
            spinner.classList.add('hidden');
            icon.classList.remove('hidden');
            btn.removeAttribute('disabled');
        }
    });
</script>
@endsection
