@props(['label' => null, 'description' => null, 'tone' => 'primary'])

{{--
    Warna track saat aktif mengikuti arti toggle-nya, bukan seragam:
    success = fitur menyala, warning = mode yang perlu disadari,
    danger = keadaan berisiko, primary/info = preferensi netral.
--}}
<label class="dash-switch tone-{{ $tone }} flex cursor-pointer items-start justify-between gap-4 rounded-xl border p-3.5"
       style="border-color: var(--hairline);">

    <span class="min-w-0">
        <span class="block text-sm" style="color: var(--ink);">{{ $label }}</span>
        @if ($description)
            <span class="mt-0.5 block text-xs leading-relaxed" style="color: var(--ink-soft);">{{ $description }}</span>
        @endif
    </span>

    <span class="relative shrink-0 pt-0.5">
        <input type="checkbox" class="peer sr-only" {{ $attributes }}>
        <span class="dash-switch-track block h-5 w-9 rounded-full"></span>
        <span class="dash-switch-thumb absolute left-0.5 top-1 h-4 w-4 rounded-full bg-white shadow"></span>
    </span>
</label>
