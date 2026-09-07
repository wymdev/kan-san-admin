@props(['action' => null])
<form method="GET" @if($action) action="{{ $action }}" @endif {{ $attributes->class(['ui-filter'])->merge(['aria-label' => 'Filter results']) }}>
    {{ $slot }}
</form>
