@props(['caption' => null])
<table {{ $attributes->class(['ui-table']) }}>
    @if($caption)<caption class="sr-only">{{ $caption }}</caption>@endif
    {{ $slot }}
</table>
