@php
    $background = settings('app_background_login_image') ? safe_image_url(settings('app_background_login_image')) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    <title>{{ $title ?? 'Masuk' }} · {{ settings('app_name_short') ?: 'Portfolio' }}</title>
    <link rel="icon" href="{{ safe_image_url(settings('app_favicon')) }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/dashboard.js'])
    @include('partials.design-tokens', ['primary' => settings()->color()])
    @livewireStyles

    <style>
        /* Panel kiri: gambar latar dengan lapisan gelap bertint warna aksen,
           supaya foto apa pun tetap menyatu dengan sistem warna. */
        .auth-visual::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(140deg,
                color-mix(in srgb, var(--primary) 55%, transparent),
                rgba(10, 14, 22, 0.88));
        }
    </style>
</head>
<body class="antialiased">

    <div id="bg-grid" aria-hidden="true"></div>

    <div class="relative z-10 min-h-screen lg:grid lg:grid-cols-[1.1fr_1fr]">

        {{-- Panel kiri hanya tampil di layar lebar; di ponsel ruang diberikan
             sepenuhnya ke formulir. --}}
        <aside class="auth-visual relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between lg:p-12"
               @if ($background) style="background-image: url('{{ $background }}'); background-size: cover; background-position: center;" @endif>

            <div class="relative z-10">
                <span class="mono text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                    {{ settings('app_name_short') ?: 'Portfolio' }}
                </span>
            </div>

            <div class="relative z-10 max-w-md">
                <h1 class="text-4xl font-bold leading-tight text-white">
                    {{ settings('app_name') ?: 'Panel Kendali Portfolio' }}
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-white/75">
                    {{ settings('app_description') ?: 'Kelola projects, tech stack, spesialisasi, dan seluruh konten situs dari satu tempat.' }}
                </p>
            </div>

            <div class="relative z-10 flex items-center gap-4">
                @foreach ([
                    'github_link' => 'fa-brands fa-github',
                    'linkedin_link' => 'fa-brands fa-linkedin-in',
                    'instagram_link' => 'fa-brands fa-instagram',
                    'x_twitter_link' => 'fa-brands fa-x-twitter',
                ] as $field => $icon)
                    @if (settings($field))
                        <a href="{{ settings($field) }}" target="_blank" rel="noopener noreferrer"
                           class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/20 text-white/70 transition hover:border-white/50 hover:text-white">
                            <i class="{{ $icon }} text-sm"></i>
                        </a>
                    @endif
                @endforeach
            </div>
        </aside>

        <main class="flex min-h-screen items-center justify-center px-6 py-12">
            {{ $slot }}
        </main>
    </div>

    {{-- Isi overlay offline; dipasang ke DOM oleh dashboard.js saat koneksi putus. --}}
    <template id="dash-offline-template">@include('offline')</template>

    @livewireScripts
</body>
</html>
