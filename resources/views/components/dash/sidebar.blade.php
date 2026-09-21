

@php
    $groups = [
        'Konten' => [
            ['label' => 'Dashboard', 'icon' => 'fa-solid fa-chart-line', 'route' => 'dashboard.home', 'pattern' => 'dashboard.home'],
            ['label' => 'Projects', 'icon' => 'fa-solid fa-folder-open', 'route' => 'dashboard.projects', 'pattern' => 'dashboard.projects*'],
            ['label' => 'Tech Stack', 'icon' => 'fa-solid fa-layer-group', 'route' => 'dashboard.tech-stacks', 'pattern' => 'dashboard.tech-stacks'],
            ['label' => 'Spesialisasi', 'icon' => 'fa-solid fa-bullseye', 'route' => 'dashboard.specializations', 'pattern' => 'dashboard.specializations'],
            ['label' => 'Pesan Masuk', 'icon' => 'fa-solid fa-envelope', 'route' => 'dashboard.contacts', 'pattern' => 'dashboard.contacts'],
        ],
        'Sistem' => [
            ['label' => 'Profil', 'icon' => 'fa-solid fa-user', 'route' => 'dashboard.profile', 'pattern' => 'dashboard.profile'],
            ['label' => 'Pengaturan', 'icon' => 'fa-solid fa-sliders', 'route' => 'dashboard.settings', 'pattern' => 'dashboard.settings'],
            ['label' => 'Backup', 'icon' => 'fa-solid fa-database', 'route' => 'dashboard.backup', 'pattern' => 'dashboard.backup'],
            ['label' => 'Notifikasi', 'icon' => 'fa-solid fa-bell', 'route' => 'dashboard.notifications', 'pattern' => 'dashboard.notifications'],
        ],
    ];
@endphp

<aside :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="dash-sidebar fixed inset-y-0 left-0 z-40 flex flex-col">

    <div class="flex h-[64px] shrink-0 items-center gap-2.5 px-4">
        <a href="{{ route('dashboard.home') }}" wire:navigate
           class="dash-rail-item flex min-w-0 flex-1 items-center gap-2.5"
           data-rail-tip="{{ settings('app_name_short') ?: 'Portfolio' }}">

            @if (settings('app_logo') && is_array(settings('app_logo')) && count(settings('app_logo')))
                <img src="{{ safe_image_url(settings('app_logo')[0]) }}" alt=""
                     class="h-7 w-7 shrink-0 rounded-lg object-cover">
            @else
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-[11px] font-medium text-white"
                      style="background-color: var(--primary);">
                    {{ strtoupper(substr(settings('app_name_short') ?: 'P', 0, 1)) }}
                </span>
            @endif

            <span class="dash-rail-label truncate text-sm font-medium" style="color: var(--ink);">
                {{ Str::limit(settings('app_name_short') ?: 'PORTFOLIO', 16) }}
            </span>
        </a>

        {{-- Hanya untuk drawer mobile. Penyembunyiannya lewat .dash-drawer-close
             di app.css, bukan utility lg:hidden — .dash-icon-btn berada di luar
             cascade layer sehingga display-nya selalu menang atas utility. --}}
        <button type="button" @click="open = false" class="dash-icon-btn dash-drawer-close" aria-label="Tutup menu">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>

    <nav class="flex-1 space-y-5 overflow-y-auto overflow-x-hidden px-3 pb-4">
        @foreach ($groups as $groupLabel => $items)
            <div class="space-y-0.5">
                <p class="dash-section-label dash-rail-label mono px-3 pb-1.5 text-[10px] uppercase tracking-[0.12em]"
                   style="color: var(--ink-soft);">
                    {{ $groupLabel }}
                </p>
                
                @foreach ($items as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate
                       @class(['dash-nav-link', 'dash-rail-item', 'active' => request()->routeIs($item['pattern'])])
                       data-rail-tip="{{ $item['label'] }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span class="dash-rail-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="shrink-0 border-t px-3 py-3" style="border-color: var(--hairline);">
        {{-- Konfirmasi keluar ditangani komponen Livewire bersama, bukan submit langsung. --}}
        <button type="button"
                x-on:click="$dispatch('confirm-logout')"
                class="dash-nav-link dash-rail-item w-full"
                data-rail-tip="Keluar">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span class="dash-rail-label">Keluar</span>
        </button>
    </div>
</aside>
