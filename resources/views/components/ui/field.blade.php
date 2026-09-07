@props(['name', 'label', 'required' => false, 'hint' => null])
<div {{ $attributes->class(['form-group']) }}>
    <label for="{{ $name }}" class="form-label">{{ $label }} @if($required)<span class="text-danger" aria-hidden="true">*</span>@endif</label>
    {{ $slot }}
    @if($hint)<p id="{{ $name }}-hint" class="ui-field-hint">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $name }}-error" class="ui-field-error">{{ $message }}</p>@enderror
</div>
