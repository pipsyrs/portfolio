@php
    $owner = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-locale="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // Terapkan tema sebelum cat pertama supaya tidak ada kedipan putih.
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <title>{{ $title ?? 'Dashboard' }} · {{ settings('app_name_short') ?: 'Portfolio' }}</title>
    <link rel="icon" href="{{ safe_image_url(settings('app_favicon')) }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    {{-- Bobot dibatasi pada yang benar-benar dipakai dashboard (400-700). --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/dashboard.js'])
    @include('partials.design-tokens', ['primary' => settings()->color()])
    @livewireStyles
</head>
<body class="antialiased">

    <div id="bg-grid" aria-hidden="true"></div>

    <div id="dash-loading" aria-hidden="true">
        <div class="flex flex-col items-center gap-3">
            <div class="dash-spinner"></div>
            <span class="mono text-xs" style="color: var(--ink-soft);">MEMUAT</span>
        </div>
    </div>

    {{-- Bentuk objek, bukan `collapsed && '...'`: ekspresi itu menghasilkan
         boolean false saat rail terbuka, dan penanganannya bergantung versi. --}}
    <div x-data="dashShell" :class="{ 'is-collapsed': collapsed }" class="relative z-10 min-h-screen">

        <x-dash.sidebar />

        {{-- Latar gelap hanya untuk drawer mobile; di desktop sidebar menempel. --}}
        <div x-cloak x-show="open" x-transition.opacity
             @click="open = false"
             class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

        {{-- Kolom setinggi layar dengan <main> yang memuai, supaya footer tetap
             menempel di bawah walau isi halamannya pendek. --}}
        <div class="dash-main flex min-h-screen flex-col">
            <x-dash.topbar :owner="$owner" />

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>

            <footer class="px-4 pb-8 sm:px-6 lg:px-8">
                <p class="mono text-[11px]" style="color: var(--ink-soft);">
                    {{ settings('app_name') ?: 'Portfolio' }} · v1.0 ·
                    <a href="{{ route('index') }}" target="_blank" class="hover:underline">lihat situs publik</a>
                </p>
            </footer>
        </div>
    </div>

    {{-- Konfirmasi keluar. Memakai shell dialog yang sama dengan konfirmasi
         hapus, tapi digerakkan Alpine karena tidak ada state server yang
         perlu dibaca lebih dulu. --}}
    <div x-data="{ ask: false }"
         x-on:confirm-logout.window="ask = true"
         x-on:keydown.escape.window="ask = false">

        <template x-if="ask">
            <div class="dash-dialog fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
                 role="dialog" aria-modal="true" aria-label="Konfirmasi keluar">

                <div class="dash-dialog-scrim absolute inset-0" @click="ask = false"></div>

                <div class="dash-dialog-panel card relative w-full max-w-md rounded-2xl p-6">
                    <div class="mb-4 flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                              style="background-color: color-mix(in srgb, var(--warning) 14%, transparent); color: var(--warning);">
                            <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                        </span>
                        <div class="min-w-0 pt-1.5">
                            <h3 class="text-sm font-medium" style="color: var(--ink);">Keluar dari dashboard?</h3>
                        </div>
                    </div>

                    <p class="text-sm leading-relaxed" style="color: var(--ink-soft);">
                        Sesi di perangkat ini akan diakhiri. Perubahan yang belum disimpan akan hilang.
                    </p>

                    <div class="mt-6 flex justify-end gap-2">
                        <x-dash.button variant="secondary" size="sm" @click="ask = false">Batal</x-dash.button>

                        <form method="POST" action="{{ route('dashboard.logout') }}">
                            @csrf
                            <x-dash.button type="submit" variant="danger" size="sm"
                                           icon="fa-solid fa-arrow-right-from-bracket">
                                Ya, keluar
                            </x-dash.button>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>
    {{-- Isi overlay offline; dipasang ke DOM oleh dashboard.js saat koneksi putus. --}}
    <template id="dash-offline-template">@include('offline')</template>

    @livewireScripts
</body>
</html>
