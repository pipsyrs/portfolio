@props(['tabs', 'active', 'action' => 'setTab'])

{{-- Bar tab bersama untuk Profil dan Pengaturan. --}}
<div class="flex flex-wrap gap-1 rounded-xl p-1" style="background-color: var(--surface-alt);" role="tablist">
    @foreach ($tabs as $key => $label)
        <button type="button" wire:click="{{ $action }}('{{ $key }}')"
                role="tab" aria-selected="{{ $active === $key ? 'true' : 'false' }}"
                @class(['dash-tab', 'is-active' => $active === $key])>
            {{ $label }}
        </button>
    @endforeach
</div>
