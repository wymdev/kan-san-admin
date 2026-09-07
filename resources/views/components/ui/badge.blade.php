@props(['type' => 'info'])
@php($type = in_array($type, ['success', 'danger', 'warning', 'info']) ? $type : 'info')
<span {{ $attributes->class(['ui-badge', 'ui-alert--'.$type]) }}>{{ $slot }}</span>
