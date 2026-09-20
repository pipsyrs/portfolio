@props(['owner'])

<header class="dash-topbar sticky top-0 z-20 flex h-[64px] items-center gap-2 px-4 sm:px-6 lg:px-8">

    {{-- Satu tombol untuk kedua ukuran layar: di mobile membuka drawer, di
         desktop menciutkan rail. Tombol tutup drawer ada di dalam sidebar. --}}
    <button type="button" @click="toggleSidebar()" class="dash-icon-btn h-9 w-9"
            aria-label="Tampilkan atau sembunyikan menu" title="Tampilkan atau sembunyikan menu">
        <i class="fa-solid fa-bars text-sm"></i>
    </button>

    <div class="min-w-0 flex-1">
        <h1 class="truncate text-sm font-medium" style="color: var(--ink);">{{ $title ?? '' }}</h1>
    </div>

    <livewire:dashboard.notifications.bell />

    <button type="button" onclick="window.dashToggleTheme()" class="dash-icon-btn h-9 w-9" aria-label="Ganti tema">
        <i class="fa-solid fa-moon text-sm" data-theme-icon></i>
    </button>

    <div x-data="{ menu: false }" class="relative">
        <button type="button" @click="menu = !menu"
                class="flex items-center gap-2 rounded-xl border px-2 py-1.5"
                style="border-color: var(--hairline);"
                aria-label="Menu akun">
            <img src="{{ $owner?->avatarUrl() }}" alt="" class="h-7 w-7 rounded-lg object-cover">
            <span class="hidden text-xs sm:block" style="color: var(--ink);">
                {{ Str::limit($owner?->name ?? '', 14) }}
            </span>
            <i class="fa-solid fa-chevron-down text-[10px]" style="color: var(--ink-soft);"></i>
        </button>

        <div x-cloak x-show="menu" @click.outside="menu = false" x-transition.origin.top.right
             class="card absolute right-0 mt-2 w-52 rounded-xl p-1.5"
             style="background-color: var(--surface); box-shadow: 0 16px 40px rgb(0 0 0 / 0.16);">

            <div class="px-3 py-2">
                <p class="truncate text-xs font-medium" style="color: var(--ink);">{{ $owner?->name }}</p>
                <p class="truncate text-[11px]" style="color: var(--ink-soft);">{{ $owner?->email }}</p>
            </div>

            <div class="my-1 h-px" style="background-color: var(--hairline);"></div>

            <a href="{{ route('dashboard.profile') }}" wire:navigate class="dash-nav-link text-xs">
                <i class="fa-solid fa-user"></i><span>Profil</span>
            </a>
            <a href="{{ route('dashboard.settings') }}" wire:navigate class="dash-nav-link text-xs">
                <i class="fa-solid fa-sliders"></i><span>Pengaturan</span>
            </a>
            <a href="{{ route('index') }}" target="_blank" class="dash-nav-link text-xs">
                <i class="fa-solid fa-arrow-up-right-from-square"></i><span>Lihat situs</span>
            </a>

            <div class="my-1 h-px" style="background-color: var(--hairline);"></div>

            <button type="button" @click="menu = false; $dispatch('confirm-logout')" class="dash-nav-link w-full text-xs">
                <i class="fa-solid fa-arrow-right-from-bracket"></i><span>Keluar</span>
            </button>
        </div>
    </div>
</header>
