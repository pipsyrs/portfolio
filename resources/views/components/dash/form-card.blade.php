@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'action',
    'label' => 'Simpan perubahan',
    'blocking' => false,
])

{{--
    Satu grup pengaturan = satu form dengan tombol simpannya sendiri.

    Tombolnya baru muncul setelah ada input di dalam form ini yang berubah.
    Perbandingannya dilakukan di browser (nilai sekarang vs cuplikan nilai
    awal), jadi tidak ada satu pun request tambahan selama mengetik — penting
    untuk server dengan sumber daya kecil.
--}}
<form wire:submit="{{ $action }}"
      x-data="dashDirtyForm"
      x-on:form-saved.window="if ($event.detail?.form === '{{ $action }}') markClean()">

    <x-dash.card :title="$title" :subtitle="$subtitle" :icon="$icon">
        {{ $slot }}

        <div x-cloak x-show="dirty"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-5 flex flex-wrap items-center justify-end gap-3 border-t pt-4"
             style="border-color: var(--hairline);">

            <span class="mr-auto flex items-center gap-2 text-xs" style="color: var(--warning);">
                <i class="fa-solid fa-circle-dot text-[8px]"></i>
                Ada perubahan yang belum disimpan
            </span>

            <x-dash.button variant="ghost" size="sm" type="button" x-on:click="revert()">
                Batalkan
            </x-dash.button>

            <x-dash.button type="submit" size="sm" :loading-target="$action"
                           icon="fa-solid fa-floppy-disk"
                           :data-dash-blocking="$blocking ? true : null">
                {{ $label }}
            </x-dash.button>
        </div>
    </x-dash.card>
</form>
