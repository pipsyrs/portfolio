@props(['label', 'value', 'icon' => null, 'hint' => null, 'tone' => 'primary'])

@php
    $color = match ($tone) {
        'success' => 'var(--success)',
        'warning' => 'var(--warning)',
        'info' => 'var(--info)',
        'danger' => 'var(--danger)',
        default => 'var(--primary)',
    };
@endphp

<div class="card card-hover rounded-2xl p-5">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="dash-stat-label">{{ $label }}</p>
            <p class="dash-stat-value mt-2" data-countup="{{ is_numeric($value) ? $value : '' }}">{{ $value }}</p>
            @if ($hint)
                <p class="mono mt-2 text-[11px]" style="color: var(--ink-soft);">{{ $hint }}</p>
            @endif
        </div>

        @if ($icon)
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                  style="background-color: color-mix(in srgb, {{ $color }} 12%, transparent); color: {{ $color }};">
                <i class="{{ $icon }} text-sm"></i>
            </span>
        @endif
    </div>
</div>
