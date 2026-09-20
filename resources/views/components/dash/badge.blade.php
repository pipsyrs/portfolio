@props(['tone' => 'neutral', 'icon' => null])

@php
    $color = match ($tone) {
        'success' => 'var(--success)',
        'danger' => 'var(--danger)',
        'warning' => 'var(--warning)',
        'info' => 'var(--info)',
        'primary' => 'var(--primary)',
        default => 'var(--ink-soft)',
    };
@endphp

<span {{ $attributes->merge(['class' => 'dash-badge']) }}
      style="color: {{ $color }}; background-color: color-mix(in srgb, {{ $color }} 12%, transparent); border-color: color-mix(in srgb, {{ $color }} 26%, transparent);">
    @if ($icon)<i class="{{ $icon }} text-[9px]"></i>@endif
    {{ $slot }}
</span>
