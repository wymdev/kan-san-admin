@props(['name' => null])
<select @if($name) name="{{ $name }}" @endif {{ $attributes->class(['form-select'])->merge($name && $errors->has($name) ? ['aria-invalid' => 'true'] : []) }}>{{ $slot }}</select>
