@props(['title' => 'No results found', 'description' => 'Try adjusting your filters.'])
<div {{ $attributes->class(['ui-empty-state']) }}>
    <p class="font-semibold text-default-800">{{ $title }}</p>
    <p class="text-sm text-default-500 mt-1">{{ $description }}</p>
    {{ $slot }}
</div>
