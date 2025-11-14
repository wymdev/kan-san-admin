@extends('layouts.vertical', ['title' => 'Cpanel Performance'])

@section('css')
<!-- (Optional) Add extra CSS here -->
@endsection

@section('content')
<div class="mb-7 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-default-800 mb-3">Cpanel Performance Overview</h2>
        <p class="text-default-500 mb-6">Quick insight into your hosting resource usage and key stats.</p>
    </div>
    <button
        id="refresh-btn"
        class="btn bg-primary text-white font-semibold px-4 py-2 rounded flex items-center"
        type="button"
    >
        <svg class="w-5 h-5 mr-2 animate-spin hidden" id="refresh-spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path d="M4 4v5h5M20 20v-5h-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Refresh
    </button>
</div>

{{-- Resource Usage Cards --}}
<div id="stats-cards" class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 gap-6 mb-8">
    @foreach($stats as $stat)
    @php
        $colorMap = [
            'Bandwidth Usage' => 'success',
            'Disk Usage' => 'danger',
            'Email Accounts' => 'secondary',
            'FTP Accounts' => 'info',
            'SQL Databases' => 'warning',
            'Subdomains' => 'primary',
            'Addon Domains' => 'default',
            'Parked Domains' => 'default',
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
    <div class="card shadow-md bg-{{$color}}15 rounded-md overflow-hidden stat-card" data-stat="{{ $stat['name'] }}">
        <div class="card-body flex flex-col items-center py-6 px-4">
            <div class="rounded-full size-14 bg-{{$color}}-800 flex items-center justify-center mb-2">
                <i class="size-6 text-{{$color}}-50" data-lucide="{{$icon}}"></i>
            </div>
            <h5 class="mt-3 mb-2 text-center text-default-800 font-bold text-xl">
                <span class="counter-value" data-target="{{$stat['current']}}">{{$stat['current']}}</span>
                @if($stat['unit']) <span class="ml-1">{{$stat['unit']}}</span> @endif
                @if($stat['max'] && $stat['max'] != 'Unlimited')
                    <span class="text-xs text-{{$color}}-700 ml-1">/ {{$stat['max']}} {{$stat['unit']}}</span>
                @endif
            </h5>
            <p class="text-center text-sm text-default-600 font-medium mb-3">{{$stat['name']}}</p>
            <div class="w-full bg-default-200 rounded-full h-2.5 mb-1">
                <div class="bg-{{$color}}-600 h-2.5 rounded-full transition-all stat-progress"
                     style="width: {{$stat['percent'] ?? 0}}%"></div>
            </div>
            @if($stat['percent'] > 80)
                <div class="text-xs text-danger mt-2 font-semibold">
                    ⚠ High usage
                </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- SYSTEM DETAILS --}}
<div class="grid md:grid-cols-2 gap-6 mt-8">

    {{-- CPU Usage --}}
    @if(isset($cpu['data'][0]))
    @php
        $cpuData = $cpu['data'][0];
        $total = $cpuData['cpu_limit'] ?? 100;
        $used = $cpuData['cpu_usage'] ?? 0;
        $percent = ($used && $total) ? ($used / $total) * 100 : 0;
    @endphp
    <div class="card">
        <div class="card-body">
            <h4 class="font-semibold text-lg mb-3">CPU Usage</h4>
            <div class="text-2xl font-bold mb-2">{{ number_format($used, 1) }} / {{ $total }}</div>
            <div class="w-full bg-default-200 rounded-full h-2.5 mb-1">
                <div class="bg-primary-600 h-2.5 rounded-full transition-all"
                    style="width: {{$percent}}%"></div>
            </div>
            <span class="font-medium text-default-700">{{ number_format($percent) }}%</span>
        </div>
    </div>
    @endif

    {{-- Process List --}}
    @if(isset($procs['data']) && is_array($procs['data']))
    <div class="card">
    <div class="card-body overflow-x-auto">
    <h4 class="font-semibold text-lg mb-3">Top Processes</h4>
    <table class="table-auto w-full text-sm">
        <thead>
            <tr>
                <th>PID</th>
                <th>User</th>
                <th>CPU</th>
                <th>Command</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($procs['data'], 0, 5) as $proc)
                <tr>
                    <td>{{ $proc['pid'] ?? '-' }}</td>
                    <td>{{ $proc['user'] ?? '-' }}</td>
                    <td>{{ $proc['cpu'] ?? '-' }}%</td>
                    <td class="truncate" title="@if(isset($proc['command']) && is_array($proc['command'])){{ implode(', ', $proc['command']) }}@else{{ $proc['command'] ?? '-' }}@endif">
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
    @endif

    {{-- Cron Jobs --}}
    @if(isset($cron['data']) && is_array($cron['data']))
    <div class="card">
        <div class="card-body">
            <h4 class="font-semibold text-lg mb-3">Cron Jobs</h4>
            <ul class="list-disc ml-6">
            @forelse($cron['data'] as $job)
                <li>
                  <span class="font-semibold">
                    @if(isset($job['command']) && is_array($job['command']))
                        {{ implode(', ', $job['command']) }}
                    @else
                        {{ $job['command'] ?? '-' }}
                    @endif
                  </span>
                  <span class="text-default-600">
                    ({{ $job['minute'] ?? '-' }} {{ $job['hour'] ?? '-' }} {{ $job['day'] ?? '-' }} {{ $job['month'] ?? '-' }} {{ $job['weekday'] ?? '-' }})
                  </span>
                </li>
            @empty
                <li><span class="text-default-500">No cron jobs found</span></li>
            @endforelse
            </ul>
        </div>
    </div>
    @endif

    {{-- SSL Certificates --}}
    <!-- @if(isset($ssl['data']) && count($ssl['data']))
    <div class="card">
    <div class="card-body">
    <h4 class="font-semibold text-lg mb-3">SSL Certificates</h4>
    <table class="table-auto w-full text-sm">
        <thead>
            <tr>
                <th>Domain</th>
                <th>Issuer</th>
                <th>Expires</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ssl['data'] as $cert)
                <tr>
                    <td>
                        @if(isset($cert['domains']))
                            @if(is_array($cert['domains']))
                                {{ implode(', ', $cert['domains']) }}
                            @else
                                {{ $cert['domains'] }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $cert['issuer'] ?? '-' }}</td>
                    <td>{{ $cert['expires'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    </div>
    @endif -->

    {{-- Domain Aliases --}}
    @if(isset($aliases['data']) && is_array($aliases['data']))
    <div class="card">
        <div class="card-body">
            <h4 class="font-semibold text-lg mb-3">Domain Aliases</h4>
            <ul>
                @foreach($aliases['data'] as $alias)
                    <li>
                        @if(isset($alias['domain']) && is_array($alias['domain']))
                            {{ implode(', ', $alias['domain']) }}
                        @else
                            {{ $alias['domain'] ?? '-' }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- PHP Versions --}}
    @if(isset($php['data']) && is_array($php['data']))
    <div class="card">
        <div class="card-body">
            <h4 class="font-semibold text-lg mb-3">PHP Versions</h4>
            <ul>
                @foreach($php['data'] as $ver)
                    <li>
                        @if(is_array($ver))
                            {{ implode(', ', $ver) }}
                        @else
                            {{ $ver['version'] ?? $ver }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Server Connections --}}
    @if(isset($connections['data']))
    <div class="card">
        <div class="card-body">
            <h4 class="font-semibold text-lg mb-3">Server Connections</h4>
            <ul>
                <li>Current: {{ $connections['data']['connections']['current'] ?? '-' }}</li>
                <li>Max: {{ $connections['data']['connections']['max'] ?? '-' }}</li>
            </ul>
        </div>
    </div>
    @endif

    {{-- Backups --}}
    <!-- @if(isset($backups['data']) && is_array($backups['data']))
    <div class="card">
        <div class="card-body">
            <h4 class="font-semibold text-lg mb-3">Backups</h4>
            <ul>
                @foreach($backups['data'] as $backup)
                    <li>
                        @if(isset($backup['name']) && is_array($backup['name']))
                            {{ implode(', ', $backup['name']) }}
                        @else
                            {{ $backup['name'] ?? '-' }}
                        @endif
                        ({{ $backup['date'] ?? '-' }})
                    </li>
                @endforeach
            </ul>
        </div>
    </div> -->
    <!-- @endif -->

    {{-- AutoSSL Status --}}
    <!-- @if(isset($autossl['data']) && is_array($autossl['data']))
    <div class="card">
        <div class="card-body">
            <h4 class="font-semibold text-lg mb-3">AutoSSL Status</h4>
            <ul>
                @foreach($autossl['data'] as $log)
                    <li>
                        @if(isset($log['domain']) && is_array($log['domain']))
                            {{ implode(', ', $log['domain']) }}
                        @else
                            {{ $log['domain'] ?? '-' }}
                        @endif
                        : {{ $log['status'] ?? '-' }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif -->

    {{-- Email Disk Usage --}}
    @if(isset($emailDisk['data']) && is_array($emailDisk['data']))
    <div class="card">
        <div class="card-body">
            <h4 class="font-semibold text-lg mb-3">Email Disk Usage</h4>
            <ul>
                @foreach($emailDisk['data'] as $mail)
                    <li>
                        @if(isset($mail['mailbox']) && is_array($mail['mailbox']))
                            {{ implode(', ', $mail['mailbox']) }}
                        @else
                            {{ $mail['mailbox'] ?? '-' }}
                        @endif
                        : {{ $mail['diskused'] ?? '-' }} {{ $mail['units'] ?? '' }}
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

    // Refresh button AJAX
    document.getElementById('refresh-btn').addEventListener('click', async function() {
        const btn = this;
        const spinner = document.getElementById('refresh-spinner');
        spinner.classList.remove('hidden');
        btn.setAttribute('disabled', true);

        try {
            const resp = await fetch("{{ route('cpanel.stats.refresh') }}", {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const html = await resp.text();
            document.getElementById('stats-cards').innerHTML = html;
            animateCounters();
        } catch (err) {
            alert('Failed to refresh stats!');
        } finally {
            spinner.classList.add('hidden');
            btn.removeAttribute('disabled');
        }
    });
</script>
@endsection
