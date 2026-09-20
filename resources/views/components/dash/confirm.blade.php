@props([
    'show' => false,
    'title' => 'Lanjutkan tindakan ini?',
    'icon' => 'fa-solid fa-triangle-exclamation',
    'tone' => 'danger',
    'confirmLabel' => 'Ya, lanjutkan',
    'cancelLabel' => 'Batal',
    'confirm',
    'cancel',
    'maxWidth' => 'max-w-sm',
    'blocking' => false,
])

{{--
    Satu dialog konfirmasi untuk seluruh dashboard — termasuk untuk tindakan
    yang dulu memakai wire:confirm (dialog bawaan browser).

    Pemakaian:
    <x-dash.confirm :show="$deletingId !== null"
                    title="Hapus project ini?"
                    confirm="delete" cancel="cancelDelete">
        Isi penjelasan konsekuensinya.
    </x-dash.confirm>
--}}
<x-dash.modal :show="$show" :title="$title" :icon="$icon" :tone="$tone"
              :max-width="$maxWidth" :close-action="$cancel">

    {{ $slot }}

    <x-slot:footer>
        <x-dash.button variant="secondary" size="sm" wire:click="{{ $cancel }}"
                       class="w-full sm:w-auto">
            {{ $cancelLabel }}
        </x-dash.button>

        <x-dash.button :variant="$tone === 'danger' ? 'danger' : 'primary'" size="sm"
                       wire:click="{{ $confirm }}" :loading-target="$confirm"
                       :data-dash-blocking="$blocking ? true : null"
                       class="w-full sm:w-auto">
            {{ $confirmLabel }}
        </x-dash.button>
    </x-slot:footer>
</x-dash.modal>
