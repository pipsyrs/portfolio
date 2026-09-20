@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'href' => null,
    'loadingTarget' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl transition disabled:pointer-events-none disabled:opacity-55';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-5 py-3 text-sm',
        'icon' => 'h-9 w-9 text-sm',
    ];

    $variants = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'ghost' => 'hover:bg-[color-mix(in_srgb,var(--ink)_6%,transparent)]',
        'danger' => 'text-white',
    ];

    $classes = $base . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
    $style = $variant === 'danger' ? 'background-color: var(--danger); font-weight: 600;' : ($variant === 'ghost' ? 'color: var(--ink-soft);' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if ($style) style="{{ $style }}" @endif>
        @if ($icon)<i class="{{ $icon }}"></i>@endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }} @if ($style) style="{{ $style }}" @endif>
        @if ($loadingTarget)
            <span wire:loading.remove wire:target="{{ $loadingTarget }}" class="contents">
                @if ($icon)<i class="{{ $icon }}"></i>@endif
                {{ $slot }}
            </span>
            <span wire:loading wire:target="{{ $loadingTarget }}" class="contents">
                <i class="fa-solid fa-circle-notch fa-spin"></i>
                <span>Memproses…</span>
            </span>
        @else
            @if ($icon)<i class="{{ $icon }}"></i>@endif
            {{ $slot }}
        @endif
    </button>
@endif
