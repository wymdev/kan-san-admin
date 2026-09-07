@props(['name' => null, 'type' => 'text'])
<input type="{{ $type }}" @if($name) name="{{ $name }}" @endif {{ $attributes->class([$type === 'checkbox' ? 'form-checkbox' : ($type === 'radio' ? 'form-radio' : ($type === 'hidden' ? '' : 'form-input'))])->merge($name && $errors->has($name) ? ['aria-invalid' => 'true'] : []) }} />
