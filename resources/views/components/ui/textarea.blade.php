@props(['name' => null])
<textarea @if($name) name="{{ $name }}" @endif {{ $attributes->class(['form-input'])->merge($name && $errors->has($name) ? ['aria-invalid' => 'true'] : []) }}>{{ $slot }}</textarea>
