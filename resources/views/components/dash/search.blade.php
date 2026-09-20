@props(['placeholder' => 'Cari…', 'target' => 'search'])

<div class="relative">
    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs"
       style="color: var(--ink-soft);"></i>

    <input type="search" placeholder="{{ $placeholder }}"
           {{ $attributes->merge(['class' => 'dash-input pl-9 pr-9']) }}>

    <span wire:loading wire:target="{{ $target }}"
          class="absolute right-3 top-1/2 -translate-y-1/2">
        <i class="fa-solid fa-circle-notch fa-spin text-xs" style="color: var(--primary);"></i>
    </span>
</div>
