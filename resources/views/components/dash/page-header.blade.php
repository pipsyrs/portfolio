@props(['title', 'subtitle' => null, 'eyebrow' => null])

{{-- Kepala halaman yang sebelumnya disalin di sembilan view. --}}
<div class="flex flex-wrap items-end justify-between gap-4">
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="mono mb-1.5 text-[10px] uppercase tracking-[0.14em]" style="color: var(--ink-soft);">
                {{ $eyebrow }}
            </p>
        @endif

        <h2 class="text-xl font-medium tracking-tight" style="color: var(--ink);">{{ $title }}</h2>

        @if ($subtitle)
            <p class="mt-1 text-sm" style="color: var(--ink-soft);">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
