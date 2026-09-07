@props(['type' => 'info', 'title' => null])
@php($tone = ['error' => 'danger', 'success' => 'success', 'warning' => 'warning', 'danger' => 'danger', 'info' => 'info'][$type] ?? 'info')
<div {{ $attributes->class(['ui-alert', 'ui-alert--'.$tone]) }} role="{{ $tone === 'danger' ? 'alert' : 'status' }}">
    @if($title)<p class="font-semibold mb-1">{{ $title }}</p>@endif
    {{ $slot }}
</div>
