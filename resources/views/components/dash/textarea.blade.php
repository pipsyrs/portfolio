@props(['label' => null, 'hint' => null, 'name' => null, 'rows' => 4, 'required' => false])

@php $id = $name ?? 'f-' . Str::random(6); @endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="dash-label">
            {{ $label }}
            @if ($required)<span style="color: var(--danger);">*</span>@endif
        </label>
    @endif

    <textarea id="{{ $id }}" rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'dash-input resize-y' . ($name && $errors->has($name) ? ' is-invalid' : '')]) }}></textarea>

    @if ($hint)<p class="dash-hint">{{ $hint }}</p>@endif

    @if ($name)
        @error($name)
            <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
        @enderror
    @endif
</div>
