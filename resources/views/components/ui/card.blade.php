@props(['title' => null])
<section {{ $attributes->class(['card']) }}>
    @if($title || isset($actions))
        <div class="card-header">
            @if($title)<h2 class="card-title">{{ $title }}</h2>@endif
            @isset($actions)<div class="ui-actions">{{ $actions }}</div>@endisset
        </div>
    @endif
    <div class="card-body">{{ $slot }}</div>
    @isset($footer)<div class="card-footer">{{ $footer }}</div>@endisset
</section>
