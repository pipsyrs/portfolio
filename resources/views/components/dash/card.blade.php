@props(['title' => null, 'subtitle' => null, 'icon' => null, 'padding' => 'p-5'])

<section {{ $attributes->merge(['class' => 'card rounded-2xl']) }}>
    @if ($title || isset($actions))
        <header class="flex items-start justify-between gap-4 border-b px-5 py-4" style="border-color: var(--hairline);">
            <div class="min-w-0">
                <h2 class="flex items-center gap-2 text-sm font-medium" style="color: var(--ink);">
                    @if ($icon)<i class="{{ $icon }} text-xs" style="color: var(--primary);"></i>@endif
                    {{ $title }}
                </h2>
                @if ($subtitle)
                    <p class="mt-1 text-xs" style="color: var(--ink-soft);">{{ $subtitle }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div class="{{ $padding }}">{{ $slot }}</div>
</section>
