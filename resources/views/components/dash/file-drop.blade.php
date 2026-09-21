@props([
    'label' => null,
    'name',
    'current' => null,
    'hint' => null,
    'accept' => 'image/*',
    'preview' => true,
])

<div x-data="{ dragging: false }">
    @if ($label)<label class="dash-label">{{ $label }}</label>@endif

    <div @dragover.prevent="dragging = true"
         @dragleave.prevent="dragging = false"
         @drop="dragging = false"
         :class="dragging && 'ring-2'"
         class="relative flex items-center gap-4 rounded-xl border border-dashed p-4 transition"
         style="border-color: var(--hairline);">

        @if ($preview)
            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl"
                 style="background-color: var(--surface-alt);">
                @if ($current)
                    <img src="{{ $current }}" alt="" class="h-full w-full object-cover">
                @else
                    <i class="fa-solid fa-image text-sm" style="color: var(--ink-soft);"></i>
                @endif
            </div>
        @endif

        <div class="min-w-0 flex-1">
            <input type="file" accept="{{ $accept }}"
                   {{ $attributes }}
                   class="block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-white"
                   style="color: var(--ink-soft);">

            <div wire:loading wire:target="{{ $name }}" class="mt-2 flex items-center gap-2">
                <div class="dash-skeleton h-1.5 flex-1 rounded-full"></div>
                <span class="mono text-[10px]" style="color: var(--ink-soft);">mengunggah</span>
            </div>

            @if ($hint)<p class="dash-hint">{{ $hint }}</p>@endif

            @error($name)
                <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
