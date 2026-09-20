@props(['icon' => 'fa-solid fa-inbox', 'title' => 'Belum ada data', 'description' => null])

<div class="flex flex-col items-center justify-center px-6 py-16 text-center">
    <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl"
          style="background-color: color-mix(in srgb, var(--primary) 10%, transparent); color: var(--primary);">
        <i class="{{ $icon }} text-lg"></i>
    </span>

    <h3 class="text-sm font-medium" style="color: var(--ink);">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1.5 max-w-sm text-xs leading-relaxed" style="color: var(--ink-soft);">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
