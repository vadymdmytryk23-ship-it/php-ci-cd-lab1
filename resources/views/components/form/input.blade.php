@props(['name', 'label', 'type' => 'text', 'value' => ''])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           value="{{ $type === 'password' ? '' : old($name, $value) }}"
           @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
           {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    @error($name)
        <div id="{{ $name }}-error" class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
