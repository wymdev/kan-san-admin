@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])
@php($variant = in_array($variant, ['primary', 'secondary', 'danger', 'success']) ? $variant : 'primary')
@if($href)
    <a href="{{ $href }}" {{ $attributes->class(['btn', 'ui-button', 'ui-button--'.$variant]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class(['btn', 'ui-button', 'ui-button--'.$variant]) }}>{{ $slot }}</button>
@endif
