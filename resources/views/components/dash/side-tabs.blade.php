@props(['groups', 'active', 'action' => 'setTab'])

{{--
    Navigasi vertikal untuk halaman Pengaturan. Tab dikelompokkan supaya
    daftarnya terbaca sebagai beberapa bagian pendek, bukan satu deret panjang.
    Di layar sempit daftar yang sama menggulir mendatar.

    $groups: ['Nama Grup' => ['key' => ['label' => '...', 'icon' => '...']]]
--}}
<nav class="dash-side-tabs" role="tablist" aria-orientation="vertical">
    @foreach ($groups as $group => $tabs)
        <p class="dash-side-tabs-heading">{{ $group }}</p>

        @foreach ($tabs as $key => $tab)
            <button type="button" wire:click="{{ $action }}('{{ $key }}')"
                    role="tab" aria-selected="{{ $active === $key ? 'true' : 'false' }}"
                    @class(['dash-side-tab', 'is-active' => $active === $key])>
                <i class="{{ $tab['icon'] }} dash-side-tab-icon"></i>
                <span>{{ $tab['label'] }}</span>
            </button>
        @endforeach
    @endforeach
</nav>
