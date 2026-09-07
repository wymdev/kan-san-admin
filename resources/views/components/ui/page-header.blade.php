@props(['title', 'subtitle' => null])
<header {{ $attributes->class(['ui-page-header', 'print:hidden']) }}>
    <div>
        @if($subtitle)<p class="ui-eyebrow">{{ $subtitle }}</p>@endif
        <h1>{{ $title }}</h1>
    </div>
    @isset($actions)<div class="ui-actions">{{ $actions }}</div>@endisset
</header>
