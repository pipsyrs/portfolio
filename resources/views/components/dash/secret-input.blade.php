@props(['label', 'field', 'stored' => false, 'hint' => null, 'placeholder' => null])

{{--
    Input untuk nilai rahasia. Nilai yang sudah tersimpan tidak pernah dikirim
    ke browser — field selalu mulai kosong, dan kosong berarti "jangan ubah".
--}}
<div x-data="{ show: false }">
    <div class="mb-1.5 flex items-center justify-between gap-2">
        <label class="dash-label mb-0">{{ $label }}</label>

        @if ($stored)
            <div class="flex items-center gap-2">
                <x-dash.badge tone="success" icon="fa-solid fa-lock">TERSIMPAN</x-dash.badge>

                <button type="button" wire:click="confirmClearSecret('{{ $field }}')"
                        class="text-[10px] transition hover:underline" style="color: var(--danger);">
                    Kosongkan
                </button>
            </div>
        @else
            <x-dash.badge tone="warning">BELUM DIISI</x-dash.badge>
        @endif
    </div>

    <div class="relative">
        <input :type="show ? 'text' : 'password'"
               wire:model="secrets.{{ $field }}"
               autocomplete="off" spellcheck="false"
               placeholder="{{ $placeholder ?? ($stored ? 'Biarkan kosong untuk mempertahankan nilai lama' : 'Belum diisi') }}"
               @class(['dash-input mono pr-10', 'is-invalid' => $errors->has('secrets.'.$field)])>

        <button type="button" @click="show = !show"
                class="absolute right-3 top-1/2 -translate-y-1/2"
                style="color: var(--ink-soft);"
                :aria-label="show ? 'Sembunyikan' : 'Tampilkan'">
            <i class="fa-solid text-xs" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
        </button>
    </div>

    @if ($hint)<p class="dash-hint">{{ $hint }}</p>@endif

    @error('secrets.'.$field)
        <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
    @enderror
</div>
