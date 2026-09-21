@php
    use App\Support\AccentPalette;

    $user = \App\Models\User::owner();
    $primaryColor = settings()->color();
    $accent = (new AccentPalette($primaryColor))->toArray();
    $currentLocale = app()->getLocale();
    $seoKeywords = settings('seo_keywords') ?: $user?->keywords;
    $seoImage = safe_image_url(settings('seo_og_image') ?: ($user->foto ?? 'images/seo-banner.jpg'));

    // Tautan nav hanya dibuat untuk bagian yang benar-benar dirender di index.blade.php.
    $navItems = collect([
        'hero' => 'Home',
        'about' => 'About',
        'tech' => 'Skills',
        'security' => 'Security',
        'specialis' => 'Specialties',
        'careers' => 'Careers',
        'certifications' => 'Certifications',
        'projects' => 'Projects',
        'contact' => 'Contact',
    ])->filter(fn (string $label, string $key) => settings()->sectionEnabled($key));

    $hasCv = filled($user?->cv_file);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" data-locale="{{ $currentLocale }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <script>
        document.documentElement.classList.add('lp-js');

        // Tema dan bahasa dipasang sebelum paint pertama supaya tidak berkedip.
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        var savedLocale = localStorage.getItem('locale');
        if (savedLocale === 'en' || savedLocale === 'id') {
            document.documentElement.setAttribute('data-locale', savedLocale);
            document.documentElement.setAttribute('lang', savedLocale);
        }
    </script>

    <title>{{ $user->name ?? 'Portfolio' }} | {{ $user->specialis ?? 'Fullstack Web Developer' }}</title>
    <link rel="icon" href="{{ safe_image_url(settings('app_favicon')) }}">

    <meta name="title" content="{{ $user->name ?? 'Portfolio' }} | {{ $user->specialis ?? 'Fullstack Web Developer' }}">
    <meta name="description" content="{{ $user->headline ?? 'Portfolio of John Doe, a passionate Fullstack Web Developer specializing in Laravel, Vue.js, and Tailwind CSS. Building robust, scalable, and beautifully designed web applications.' }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="{{ $user->name }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $user->name ?? config('app.name', 'Portfolio') }} | {{ $user->specialis ?? 'Fullstack Web Developer' }}">
    <meta property="og:description" content="{{ $user->headline ?? 'Portfolio of John Doe, a passionate Fullstack Web Developer specializing in Laravel, Vue.js, and Tailwind CSS.' }}">
    <meta property="og:image" content="{{ $seoImage }}">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $user->name ?? config('app.name', 'Portfolio') }} | {{ $user->specialis ?? 'Fullstack Web Developer' }}">
    <meta property="twitter:description" content="{{ $user->headline ?? 'Portfolio of John Doe, a passionate Fullstack Web Developer specializing in Laravel, Vue.js, and Tailwind CSS.' }}">
    <meta property="twitter:image" content="{{ $seoImage }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    @include('partials.design-tokens', ['primary' => $primaryColor])

    <style>
        /* clip, bukan hidden: hidden membuat scroll container dan mematikan position:sticky. */
        html, body { overflow-x: clip; }

        .lp {
            --gutter: clamp(1.25rem, 5vw, 5rem);
            --measure: 1240px;
            --nav-h: 72px;
            --r-tag: 7px;
            --r-ctl: 12px;
            --r-card: 20px;
            --r-slab: 28px;
            --rule: color-mix(in srgb, var(--ink) 11%, transparent);
            --accent-text: {{ $accent['text_light'] }};
            --on-primary: {{ $accent['on_primary'] }};
            --shadow-sm: 0 1px 2px color-mix(in srgb, var(--ink) 6%, transparent);
            --shadow-md: 0 1px 2px color-mix(in srgb, var(--ink) 5%, transparent),
                         0 16px 38px -20px color-mix(in srgb, var(--ink) 30%, transparent);
            --shadow-lg: 0 30px 80px -34px color-mix(in srgb, var(--ink) 40%, transparent);
            --inner-lift: 0 0 transparent;

            /* Kaca: permukaan translusen + blur. Nilai terang dijaga cukup
               pekat supaya teks var(--ink) tetap lolos 4.5:1 di atas aura. */
            --glass-bg: rgba(255, 255, 255, 0.68);
            --glass-brd: rgba(255, 255, 255, 0.72);
            --glass-sheen: rgba(255, 255, 255, 0.85);
            --glass-blur: 18px;
        }
        .dark .lp {
            --rule: color-mix(in srgb, var(--ink) 15%, transparent);
            --accent-text: {{ $accent['text_dark'] }};
            --shadow-sm: 0 0 transparent;
            --shadow-md: 0 20px 44px -26px rgba(0, 0, 0, 0.9);
            --shadow-lg: 0 34px 90px -38px rgba(0, 0, 0, 0.95);
            --inner-lift: inset 0 1px 0 rgba(255, 255, 255, 0.055);

            --glass-bg: rgba(18, 24, 38, 0.55);
            --glass-brd: rgba(255, 255, 255, 0.10);
            --glass-sheen: rgba(255, 255, 255, 0.07);
            --glass-blur: 20px;
        }

        .lp :focus-visible {
            outline: 2px solid var(--accent-text);
            outline-offset: 3px;
            border-radius: 4px;
        }

        .lp-shell {
            width: 100%;
            max-width: var(--measure);
            margin-inline: auto;
            padding-inline: var(--gutter);
        }

        /* Judul dan isi boleh memenggal kata panjang daripada menembus wadahnya. */
        .lp h1, .lp h2, .lp h3, .lp p, .lp li, .lp dd, .lp dt, .lp label { overflow-wrap: break-word; }
        .lp .prose-content { overflow-wrap: anywhere; }
        .lp img, .lp svg { max-inline-size: 100%; }

        /* ------------------------------------------------------------------
           Lapisan ambient

           Kaca hanya terbaca sebagai kaca kalau ada sesuatu di belakangnya.
           Tiga lapis: mesh aksen, grid blueprint, lalu vignette penenang.
           Semuanya fixed dan pointer-events none, jadi tidak pernah ikut
           dihitung ulang saat konten bergerak.
           ------------------------------------------------------------------ */
        #lp-aura {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image:
                radial-gradient(42rem 30rem at 12% -6%, color-mix(in srgb, var(--primary) 34%, transparent), transparent 62%),
                radial-gradient(34rem 26rem at 88% 6%, color-mix(in srgb, var(--primary) 20%, transparent), transparent 66%),
                radial-gradient(38rem 30rem at 72% 78%, color-mix(in srgb, var(--primary) 14%, transparent), transparent 68%);
            opacity: 0.55;
        }
        .dark #lp-aura { opacity: 0.4; }

        /* Hanyut sangat lambat: cukup untuk membuat kaca terasa hidup, terlalu
           pelan untuk menarik perhatian dari teks. */
        @media (prefers-reduced-motion: no-preference) {
            #lp-aura { animation: lp-drift 26s ease-in-out infinite alternate; }
            @keyframes lp-drift {
                from { transform: translate3d(0, 0, 0) scale(1); }
                to { transform: translate3d(-2%, 1.5%, 0) scale(1.06); }
            }
        }

        /* Grid landing dibuat lebih tegas daripada versi dasbor supaya terlihat
           menembus kartu kaca; selector lebih spesifik agar menang tanpa !important. */
        body.lp #bg-grid {
            background-image:
                linear-gradient(color-mix(in srgb, var(--ink) 9%, transparent) 1px, transparent 1px),
                linear-gradient(90deg, color-mix(in srgb, var(--ink) 9%, transparent) 1px, transparent 1px);
            background-size: 52px 52px;
            -webkit-mask-image: radial-gradient(ellipse 85% 65% at 50% 0%, black, transparent 78%);
            mask-image: radial-gradient(ellipse 85% 65% at 50% 0%, black, transparent 78%);
        }

        #lp-veil {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image: radial-gradient(ellipse 120% 80% at 50% 40%, transparent 40%, color-mix(in srgb, var(--surface) 70%, transparent));
        }

                #lp-main { position: relative; z-index: 1; }

        /* scroll-margin menahan awal section supaya tidak tertutup navbar fixed saat lompat anchor. */
        .lp-section {
            position: relative;
            z-index: 1;
            padding-block: clamp(4.5rem, 10vw, 9rem);
            scroll-margin-block-start: calc(var(--nav-h) + 8px);
        }
        .lp-section--open { padding-block: clamp(5.5rem, 13vw, 12rem); }
        .lp-section--tight { padding-block: clamp(3rem, 6vw, 5rem); }

        /* Pembatas antar bagian: hairline yang memudar di kedua ujung, bukan
           garis penuh yang membelah halaman. */
        .lp-section + .lp-section::before {
            content: '';
            position: absolute;
            inset-block-start: 0;
            inset-inline: var(--gutter);
            block-size: 1px;
            background-image: linear-gradient(90deg, transparent, var(--rule) 18%, var(--rule) 82%, transparent);
        }

        /* Motif pembuka bagian: pil mono dengan nomor indeks di lingkaran aksen. */
        .lp-slug {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.34rem 0.85rem 0.34rem 0.36rem;
            border: 1px solid var(--hairline);
            border-radius: 999px;
            background-color: var(--surface-alt);
            box-shadow: var(--inner-lift);
            font-family: var(--font-mono);
            font-size: 0.6875rem;
            font-weight: 500;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--ink-soft);
        }
        .lp-slug .idx {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            inline-size: 1.5rem;
            block-size: 1.5rem;
            flex: none;
            border-radius: 999px;
            background-color: var(--primary);
            color: var(--on-primary);
            font-size: 0.625rem;
            font-weight: 600;
            letter-spacing: 0;
        }

        .lp-display {
            font-size: clamp(2.75rem, 10vw, 6.75rem);
            line-height: 0.92;
            letter-spacing: -0.052em;
            font-weight: 800;
            color: var(--ink);
        }
        .lp-h2 {
            font-size: clamp(2rem, 5.6vw, 3.6rem);
            line-height: 1.04;
            letter-spacing: -0.038em;
            font-weight: 700;
            color: var(--ink);
        }
        .lp-h3 {
            font-size: clamp(1.15rem, 2.4vw, 1.5rem);
            line-height: 1.25;
            letter-spacing: -0.02em;
            font-weight: 600;
            color: var(--ink);
        }
        .lp-lead {
            font-size: clamp(1.0625rem, 1.6vw, 1.25rem);
            line-height: 1.62;
            color: var(--ink-soft);
            max-width: 58ch;
        }
        .lp-meta {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.02em;
            color: var(--ink-soft);
        }
        .lp-accent { color: var(--accent-text); }
        .lp-rule { block-size: 1px; background-image: linear-gradient(90deg, var(--rule), transparent); border: 0; }

        /* Teks bergradien dipakai hemat: satu frasa kunci per layar, tidak lebih. */
        .lp-grad {
            background-image: linear-gradient(100deg, var(--primary), color-mix(in srgb, var(--primary) 40%, var(--ink)));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* ------------------------------------------------------------------
           Sistem kaca

           Dipakai terbatas: kartu utama, ubin, navbar, dan panel modal. Baris
           teks panjang tetap solid supaya kontras baca tidak bergantung pada
           apa yang kebetulan lewat di belakangnya.
           ------------------------------------------------------------------ */
        .lp-glass {
            background-color: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur)) saturate(165%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(165%);
            border: 1px solid var(--glass-brd);
            box-shadow: var(--shadow-md), inset 0 1px 0 var(--glass-sheen);
        }

        /* Tanpa dukungan backdrop-filter, translusen hanya menghasilkan teks di
           atas latar acak. Di sana kartunya dikembalikan menjadi solid. */
        @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
            .lp-glass {
                background-color: var(--surface-alt);
                border-color: var(--hairline);
            }
        }

        .lp-card {
            position: relative;
            border-radius: var(--r-card);
            background-color: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur)) saturate(165%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(165%);
            border: 1px solid var(--glass-brd);
            box-shadow: var(--shadow-sm), inset 0 1px 0 var(--glass-sheen);
        }
        @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
            .lp-card {
                background-color: var(--surface-alt);
                border-color: var(--hairline);
            }
        }
        .lp-card-int {
            transition: border-color 0.3s var(--ease-out),
                        transform 0.35s var(--ease-out),
                        box-shadow 0.35s var(--ease-out);
        }
        @media (hover: hover) {
            .lp-card-int:hover {
                border-color: color-mix(in srgb, var(--primary) 55%, var(--glass-brd));
                transform: translateY(-4px);
                box-shadow: var(--shadow-md),
                            0 0 0 1px color-mix(in srgb, var(--primary) 22%, transparent),
                            inset 0 1px 0 var(--glass-sheen);
            }
        }

        .lp-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            min-block-size: 50px;
            padding-inline: 1.6rem;
            border-radius: var(--r-ctl);
            font-weight: 600;
            font-size: 0.9375rem;
            line-height: 1;
            overflow: hidden;
            transition: transform 0.25s var(--ease-out),
                        box-shadow 0.3s var(--ease-out),
                        border-color 0.25s var(--ease-out),
                        background-color 0.25s var(--ease-out),
                        color 0.25s var(--ease-out);
        }
        .lp-btn > * { position: relative; z-index: 1; }
        .lp-btn-primary {
            background-color: var(--primary);
            color: var(--on-primary);
            box-shadow: 0 10px 24px -12px color-mix(in srgb, var(--primary) 85%, transparent);
        }
        /* Kilau melintas sekali saat hover: penanda interaktif tanpa mengubah warna merek. */
        .lp-btn-primary::after {
            content: '';
            position: absolute;
            inset-block: 0;
            inset-inline-start: -60%;
            inline-size: 55%;
            background-image: linear-gradient(100deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transform: translateX(0);
            transition: transform 0.7s var(--ease-out);
        }
        .lp-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px -12px color-mix(in srgb, var(--primary) 85%, transparent);
        }
        .lp-btn-primary:hover::after { transform: translateX(340%); }
        .lp-btn-ghost {
            border: 1px solid var(--hairline);
            background-color: var(--surface-alt);
            color: var(--ink);
            box-shadow: var(--inner-lift);
        }
        .lp-btn-ghost:hover { transform: translateY(-2px); border-color: color-mix(in srgb, var(--primary) 45%, var(--hairline)); }
        .lp-btn:active { transform: translateY(0) scale(0.985); }
        .lp-btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; box-shadow: none; }

        .lp-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.32rem 0.62rem;
            border-radius: var(--r-tag);
            border: 1px solid var(--hairline);
            background-color: color-mix(in srgb, var(--surface) 55%, transparent);
            font-family: var(--font-mono);
            font-size: 0.6875rem;
            color: var(--ink-soft);
            max-inline-size: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .lp-chip {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-block-size: 46px;
            max-inline-size: 100%;
            padding-inline: 1rem;
            border: 1px solid var(--hairline);
            border-radius: 999px;
            background-color: var(--surface-alt);
            box-shadow: var(--inner-lift);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--ink);
            overflow-wrap: anywhere;
            text-align: start;
            transition: border-color 0.24s var(--ease-out),
                        background-color 0.24s var(--ease-out),
                        color 0.24s var(--ease-out),
                        transform 0.24s var(--ease-out);
        }
        .lp-chip i { color: var(--ink-soft); transition: color 0.24s var(--ease-out); }
        @media (hover: hover) {
            .lp-chip:hover { border-color: color-mix(in srgb, var(--primary) 45%, var(--hairline)); transform: translateY(-2px); }
            .lp-chip:hover i { color: var(--accent-text); }
        }
        .lp-chip:active { transform: translateY(0) scale(0.97); }
        .lp-chip.is-on {
            background-color: var(--primary);
            border-color: var(--primary);
            color: var(--on-primary);
            box-shadow: 0 10px 22px -14px color-mix(in srgb, var(--primary) 90%, transparent);
        }
        .lp-chip.is-on i { color: var(--on-primary); }

        .lp-icon-btn {
            inline-size: 44px;
            block-size: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--r-ctl);
            border: 1px solid var(--hairline);
            background-color: var(--surface-alt);
            box-shadow: var(--inner-lift);
            color: var(--ink-soft);
            transition: color 0.24s var(--ease-out),
                        border-color 0.24s var(--ease-out),
                        transform 0.24s var(--ease-out);
        }
        .lp-icon-btn:hover { color: var(--ink); border-color: color-mix(in srgb, var(--primary) 45%, var(--hairline)); }
        .lp-icon-btn:active { transform: scale(0.94); }

        /* ------------------------------------------------------------------
           Navbar
           ------------------------------------------------------------------ */
        #lp-progress {
            position: fixed;
            inset-block-start: 0;
            inset-inline-start: 0;
            block-size: 2px;
            inline-size: 0;
            background-image: linear-gradient(90deg, color-mix(in srgb, var(--primary) 50%, transparent), var(--primary));
            z-index: 70;
        }

        #lp-nav {
            position: fixed;
            inset-inline: 0;
            inset-block-start: 0;
            z-index: 50;
            background-color: transparent;
            border-block-end: 1px solid transparent;
            transition: transform 0.4s var(--ease-out),
                        background-color 0.3s var(--ease-out),
                        border-color 0.3s var(--ease-out);
        }
        #lp-nav.is-stuck {
            background-color: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur)) saturate(175%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(175%);
            border-block-end-color: var(--glass-brd);
            box-shadow: 0 1px 0 var(--glass-sheen) inset, var(--shadow-sm);
        }
        @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
            #lp-nav.is-stuck { background-color: var(--surface); }
        }
        /* Nav menyingkir saat pembaca bergerak turun dan kembali begitu ia naik. */
        #lp-nav.is-away { transform: translateY(-102%); }
        #lp-nav .bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            block-size: var(--nav-h);
        }

        .lp-wordmark {
            display: inline-flex;
            align-items: center;
            min-block-size: 44px;
            font-weight: 800;
            letter-spacing: -0.035em;
            font-size: 1.125rem;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-inline-size: 45vw;
        }
        .lp-wordmark i { font-style: normal; color: var(--accent-text); }

        /* Penanda aktif berupa pil yang meluncur, bukan garis yang muncul-hilang. */
        .lp-nav-links { position: relative; display: flex; align-items: center; }
        .lp-nav-pill {
            position: absolute;
            inset-block: 5px;
            inset-inline-start: 0;
            inline-size: 0;
            border-radius: 999px;
            background-color: color-mix(in srgb, var(--ink) 7%, transparent);
            opacity: 0;
            pointer-events: none;
            transition: transform 0.45s var(--ease-out),
                        width 0.45s var(--ease-out),
                        opacity 0.3s var(--ease-out);
        }
        .lp-nav-pill.is-on { opacity: 1; }
        .lp-nav-link {
            position: relative;
            z-index: 1;
            padding: 0.55rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1;
            color: var(--ink-soft);
            white-space: nowrap;
            transition: color 0.24s var(--ease-out);
        }
        .lp-nav-link:hover { color: var(--ink); }
        .lp-nav-link.is-active { color: var(--ink); font-weight: 600; }

        .lp-lang {
            position: relative;
            display: inline-flex;
            gap: 2px;
            padding: 3px;
            border-radius: 999px;
            border: 1px solid var(--hairline);
            background-color: var(--surface-alt);
            box-shadow: var(--inner-lift);
        }
        .lp-lang button {
            position: relative;
            z-index: 1;
            min-inline-size: 42px;
            block-size: 36px;
            border-radius: 999px;
            font-family: var(--font-mono);
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--ink-soft);
            transition: color 0.28s var(--ease-out);
        }
        .lp-lang button:hover { color: var(--ink); }
        .lp-lang button.is-on { color: var(--on-primary); }
        /* Thumb meluncur di belakang label, jadi ganti bahasa terbaca sebagai gerakan. */
        .lp-lang-thumb {
            position: absolute;
            inset-block: 3px;
            inset-inline-start: 3px;
            inline-size: 0;
            border-radius: 999px;
            background-color: var(--primary);
            pointer-events: none;
            transition: transform 0.4s var(--ease-spring), width 0.3s var(--ease-out);
        }

        .lp-theme-btn i { transition: transform 0.45s var(--ease-spring); }
        .lp-theme-btn:hover i { transform: rotate(-25deg) scale(1.1); }

        /* ------------------------------------------------------------------
           Drawer mobile
           ------------------------------------------------------------------ */
        #lp-sheet {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: grid;
            grid-template-columns: 1fr min(88vw, 360px);
            pointer-events: none;
        }
        #lp-sheet .backdrop {
            background-color: color-mix(in srgb, #000 52%, transparent);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            opacity: 0;
            transition: opacity 0.35s var(--ease-out);
        }
        #lp-sheet .panel {
            display: flex;
            flex-direction: column;
            background-color: var(--surface);
            border-inline-start: 1px solid var(--hairline);
            box-shadow: var(--shadow-lg);
            overflow-y: auto;
            overscroll-behavior: contain;
            transform: translateX(100%);
            transition: transform 0.45s var(--ease-out);
            padding-inline: 1.35rem;
            padding-block-start: max(1rem, env(safe-area-inset-top));
            padding-block-end: max(1.5rem, env(safe-area-inset-bottom));
        }
        #lp-sheet.is-open { pointer-events: auto; }
        #lp-sheet.is-open .backdrop { opacity: 1; }
        #lp-sheet.is-open .panel { transform: none; }

        .lp-sheet-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-block-size: 54px;
            font-size: 1.0625rem;
            font-weight: 500;
            color: var(--ink);
            border-block-end: 1px solid var(--rule);
            opacity: 0;
            transform: translateX(16px);
            transition: opacity 0.4s var(--ease-out),
                        transform 0.4s var(--ease-out),
                        color 0.2s ease;
            transition-delay: var(--stagger, 0ms);
        }
        #lp-sheet.is-open .lp-sheet-link { opacity: 1; transform: none; }
        .lp-sheet-link .n { font-family: var(--font-mono); font-size: 0.6875rem; color: var(--ink-soft); }
        .lp-sheet-link.is-active { color: var(--accent-text); }

        /* ------------------------------------------------------------------
           Modal CV: PDF dibaca di tempat, tidak membuka tab baru
           ------------------------------------------------------------------ */
        #lp-cv {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: grid;
            place-items: center;
            padding: clamp(0rem, 3vw, 2rem);
            pointer-events: none;
        }
        #lp-cv .scrim {
            position: absolute;
            inset: 0;
            background-color: color-mix(in srgb, #000 60%, transparent);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            opacity: 0;
            transition: opacity 0.3s var(--ease-out);
        }
        #lp-cv .panel {
            position: relative;
            display: flex;
            flex-direction: column;
            inline-size: min(1040px, 100%);
            block-size: min(88vh, 100%);
            background-color: var(--surface);
            border: 1px solid var(--hairline);
            border-radius: var(--r-slab);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            opacity: 0;
            transform: translateY(18px) scale(0.975);
            transition: opacity 0.35s var(--ease-out), transform 0.4s var(--ease-out);
        }
        #lp-cv.is-open { pointer-events: auto; }
        #lp-cv.is-open .scrim { opacity: 1; }
        #lp-cv.is-open .panel { opacity: 1; transform: none; }

        .lp-cv-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex: none;
            padding: 0.85rem 0.9rem 0.85rem 1.35rem;
            border-block-end: 1px solid var(--hairline);
        }
        .lp-cv-title { display: flex; align-items: center; gap: 0.7rem; min-inline-size: 0; }
        .lp-cv-title i { color: var(--accent-text); }
        .lp-cv-title span {
            font-weight: 600;
            font-size: 0.9375rem;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .lp-cv-acts { display: flex; align-items: center; gap: 0.4rem; flex: none; }
        .lp-cv-acts .lp-icon-btn { inline-size: 40px; block-size: 40px; }

        .lp-cv-stage { position: relative; flex: 1; min-block-size: 0; background-color: var(--surface-alt); }
        .lp-cv-stage iframe {
            position: absolute;
            inset: 0;
            inline-size: 100%;
            block-size: 100%;
            border: 0;
            opacity: 0;
            transition: opacity 0.4s var(--ease-out);
        }
        .lp-cv-stage.is-ready iframe { opacity: 1; }

        /* Skeleton menempati panggung sejak modal dibuka sampai PDF benar-benar
           selesai dirender, jadi tidak pernah ada panel kosong. */
        .lp-cv-skeleton {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            padding: clamp(1.25rem, 4vw, 2.5rem);
            transition: opacity 0.3s var(--ease-out);
        }
        .lp-cv-stage.is-ready .lp-cv-skeleton { opacity: 0; pointer-events: none; }
        .lp-cv-skeleton .line { block-size: 0.85rem; border-radius: 999px; flex: none; }
        .lp-cv-skeleton .block { flex: 1; border-radius: var(--r-card); }

        .lp-cv-fallback {
            position: absolute;
            inset: 0;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 2rem;
            text-align: center;
        }
        .lp-cv-stage.is-fallback .lp-cv-fallback { display: flex; }
        .lp-cv-stage.is-fallback .lp-cv-skeleton,
        .lp-cv-stage.is-fallback iframe { display: none; }

        @media (max-width: 640px) {
            #lp-cv { padding: 0; }
            #lp-cv .panel { block-size: 100%; border-radius: 0; border: 0; }
        }

        /* ------------------------------------------------------------------
           Kembali ke atas
           ------------------------------------------------------------------ */
        #lp-top {
            position: fixed;
            inset-block-end: max(1.25rem, env(safe-area-inset-bottom));
            inset-inline-end: 1.25rem;
            z-index: 45;
            background-color: var(--surface);
            opacity: 0;
            transform: translateY(14px) scale(0.85);
            pointer-events: none;
            transition: opacity 0.3s var(--ease-out), transform 0.4s var(--ease-spring);
        }
        #lp-top.is-on { opacity: 1; transform: none; pointer-events: auto; }

        /* ------------------------------------------------------------------
           Reveal saat masuk viewport
           ------------------------------------------------------------------ */
        /* Disembunyikan hanya kalau JS hidup, supaya halaman tetap terbaca saat skrip gagal. */
        .lp-js .lp [data-reveal] {
            opacity: 0;
            transform: translate3d(0, 24px, 0) scale(0.985);
            filter: blur(5px);
            transition: opacity 0.75s var(--ease-out),
                        transform 0.75s var(--ease-out),
                        filter 0.75s var(--ease-out);
            transition-delay: var(--reveal-delay, 0ms);
        }
        .lp-js .lp [data-reveal].is-in { opacity: 1; transform: none; filter: none; }

        @media (prefers-reduced-motion: reduce) {
            .lp-js .lp [data-reveal] { opacity: 1; transform: none; filter: none; transition: none; }
            html { scroll-behavior: auto; }
            .lp-btn:hover, .lp-chip:hover, .lp-card-int:hover, .lp-icon-btn:hover { transform: none; }
            .lp-btn-primary::after { display: none; }
        }

        /* Pergantian tema disapu melingkar dari tombolnya, di peramban yang mendukung. */
        @media (prefers-reduced-motion: no-preference) {
            ::view-transition-old(root),
            ::view-transition-new(root) { animation: none; mix-blend-mode: normal; }
            ::view-transition-old(root) { z-index: 0; }
            ::view-transition-new(root) { z-index: 1; animation: lp-theme-wipe 0.55s var(--ease-out); }
            @keyframes lp-theme-wipe {
                from { clip-path: circle(0% at var(--lp-wipe-x, 50%) var(--lp-wipe-y, 0%)); }
                to { clip-path: circle(140% at var(--lp-wipe-x, 50%) var(--lp-wipe-y, 0%)); }
            }
        }
    </style>
</head>
<body class="antialiased lp">

    <div id="lp-aura" aria-hidden="true"></div>
    <div id="bg-grid" aria-hidden="true"></div>
    <div id="lp-veil" aria-hidden="true"></div>

    <div id="lp-progress"></div>

    <nav id="lp-nav">
        <div class="lp-shell">
            <div class="bar">
                <a href="#hero" class="lp-wordmark">{{ $user->name ?? 'Portfolio' }}<i>.</i></a>

                <div class="hidden lg:flex items-center lp-nav-links">
                    <span class="lp-nav-pill" aria-hidden="true"></span>
                    @foreach ($navItems as $anchor => $label)
                        <a href="#{{ $anchor }}" class="lp-nav-link">{!! bt($label) !!}</a>
                    @endforeach
                </div>

                <div class="hidden lg:flex items-center gap-2">
                    <div class="lp-lang">
                        <span class="lp-lang-thumb" aria-hidden="true"></span>
                        <button type="button" class="lp-lang-btn" data-lang="id" onclick="lpSetLocale('id')">ID</button>
                        <button type="button" class="lp-lang-btn" data-lang="en" onclick="lpSetLocale('en')">EN</button>
                    </div>

                    <button type="button" class="lp-icon-btn lp-theme-btn" aria-label="{{ bt_variant('Switch theme', $currentLocale) }}">
                        <i class="fas fa-moon text-sm lp-theme-icon" aria-hidden="true"></i>
                    </button>

                    @if ($hasCv)
                        {{-- Tetap sebuah tautan: tanpa JS berkasnya masih bisa dibuka. --}}
                        <a href="{{ route('view.cv') }}" target="_blank" rel="noopener"
                           class="lp-btn lp-btn-primary ml-1" data-cv-open>
                            <i class="fas fa-file-pdf text-sm" aria-hidden="true"></i>
                            <span class="hidden xl:inline">{!! bt('Read the CV') !!}</span>
                            <span class="xl:hidden">CV</span>
                        </a>
                    @endif
                </div>

                <div class="flex lg:hidden items-center gap-2">
                    <button type="button" class="lp-icon-btn lp-theme-btn" aria-label="{{ bt_variant('Switch theme', $currentLocale) }}">
                        <i class="fas fa-moon text-sm lp-theme-icon" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="lp-icon-btn" id="lp-sheet-open" aria-controls="lp-sheet" aria-expanded="false">
                        <i class="fas fa-bars text-sm" aria-hidden="true"></i>
                        <span class="sr-only">{{ bt_variant('Menu', $currentLocale) }}</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div id="lp-sheet" aria-hidden="true" inert>
        <div class="backdrop" id="lp-sheet-backdrop"></div>
        <div class="panel" role="dialog" aria-modal="true" aria-label="{{ bt_variant('Menu', $currentLocale) }}">
            <div class="flex items-center justify-between mb-5">
                <span class="lp-meta uppercase" style="letter-spacing:0.18em">{!! bt('Menu') !!}</span>
                <button type="button" class="lp-icon-btn" id="lp-sheet-close">
                    <i class="fas fa-xmark text-sm" aria-hidden="true"></i>
                    <span class="sr-only">{{ bt_variant('Close', $currentLocale) }}</span>
                </button>
            </div>

            <nav class="flex flex-col">
                @foreach ($navItems as $anchor => $label)
                    <a href="#{{ $anchor }}" class="lp-sheet-link" data-sheet-link style="--stagger:{{ 60 + $loop->index * 45 }}ms">
                        <span>{!! bt($label) !!}</span>
                        <span class="n">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto pt-6 flex flex-col gap-3">
                <div class="lp-lang self-start">
                    <span class="lp-lang-thumb" aria-hidden="true"></span>
                    <button type="button" class="lp-lang-btn" data-lang="id" onclick="lpSetLocale('id')" style="min-inline-size:54px;block-size:44px">ID</button>
                    <button type="button" class="lp-lang-btn" data-lang="en" onclick="lpSetLocale('en')" style="min-inline-size:54px;block-size:44px">EN</button>
                </div>
                @if ($hasCv)
                    <a href="{{ route('view.cv') }}" target="_blank" rel="noopener"
                       class="lp-btn lp-btn-primary w-full" data-cv-open>
                        <i class="fas fa-file-pdf text-sm" aria-hidden="true"></i>
                        <span>{!! bt('Read the CV') !!}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if ($hasCv)
        <div id="lp-cv" aria-hidden="true" inert data-cv-src="{{ route('view.cv') }}">
            <div class="scrim" data-cv-close></div>
            <div class="panel" role="dialog" aria-modal="true" aria-labelledby="lp-cv-title">
                <div class="lp-cv-head">
                    <p class="lp-cv-title" id="lp-cv-title">
                        <i class="fas fa-file-pdf" aria-hidden="true"></i>
                        <span>{{ $user->name ?? 'Portfolio' }} &mdash; CV</span>
                    </p>
                    <div class="lp-cv-acts">
                        <a href="{{ route('view.cv') }}" download class="lp-icon-btn" aria-label="{{ bt_variant('Download', $currentLocale) }}">
                            <i class="fas fa-download text-sm" aria-hidden="true"></i>
                        </a>
                        <button type="button" class="lp-icon-btn" data-cv-close aria-label="{{ bt_variant('Close', $currentLocale) }}">
                            <i class="fas fa-xmark text-sm" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="lp-cv-stage" id="lp-cv-stage">
                    <div class="lp-cv-skeleton" aria-hidden="true">
                        <div class="sk line" style="inline-size:42%"></div>
                        <div class="sk line" style="inline-size:64%"></div>
                        <div class="sk block"></div>
                    </div>

                    <iframe id="lp-cv-frame" title="{{ bt_variant('Read the CV', $currentLocale) }}"></iframe>

                    <div class="lp-cv-fallback">
                        <i class="fas fa-file-pdf text-2xl lp-accent" aria-hidden="true"></i>
                        <p class="text-sm" style="color: var(--ink-soft); max-inline-size: 36ch;">
                            <span class="i18n-en">This browser cannot preview PDFs inline. Download the file to read it.</span><span class="i18n-id">Peramban ini tidak bisa menampilkan PDF di halaman. Unduh berkasnya untuk membaca.</span>
                        </p>
                        <a href="{{ route('view.cv') }}" download class="lp-btn lp-btn-primary">
                            <i class="fas fa-download text-sm" aria-hidden="true"></i>
                            <span>{!! bt('Download') !!}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <main id="lp-main">
        @yield('content')
    </main>

    <button type="button" id="lp-top" class="lp-icon-btn" aria-label="{{ bt_variant('Back to top', $currentLocale) }}">
        <i class="fas fa-arrow-up text-sm" aria-hidden="true"></i>
    </button>

    <script data-navigate-once>
        function lpMotionAllowed() {
            return window.matchMedia('(prefers-reduced-motion: no-preference)').matches;
        }

        function lpSyncThemeIcon() {
            const dark = document.documentElement.classList.contains('dark');
            document.querySelectorAll('.lp-theme-icon').forEach((icon) => {
                icon.classList.toggle('fa-sun', dark);
                icon.classList.toggle('fa-moon', !dark);
            });
        }

        function lpApplyTheme() {
            const root = document.documentElement;
            root.classList.toggle('dark');
            localStorage.setItem('color-theme', root.classList.contains('dark') ? 'dark' : 'light');
            lpSyncThemeIcon();
        }

        // Tema ditukar dalam satu frame tanpa transisi apa pun. Tanpa ini setiap
        // permukaan memudar dengan durasi sendiri dan pergantian terbaca sebagai lag.
        function lpToggleTheme(event) {
            const root = document.documentElement;

            if (typeof document.startViewTransition === 'function' && lpMotionAllowed()) {
                const rect = event?.currentTarget?.getBoundingClientRect();
                if (rect) {
                    root.style.setProperty('--lp-wipe-x', ((rect.left + rect.width / 2) / window.innerWidth) * 100 + '%');
                    root.style.setProperty('--lp-wipe-y', ((rect.top + rect.height / 2) / window.innerHeight) * 100 + '%');
                }
                document.startViewTransition(() => lpApplyTheme());
                return;
            }

            root.classList.add('is-theme-switching');
            lpApplyTheme();
            requestAnimationFrame(() => requestAnimationFrame(() => root.classList.remove('is-theme-switching')));
        }

        // Kedua bahasa sudah ada di DOM (lihat bt()), jadi pergantian hanya membalik atribut.
        function lpSetLocale(locale) {
            if (locale !== 'en' && locale !== 'id') return;
            document.documentElement.setAttribute('data-locale', locale);
            document.documentElement.setAttribute('lang', locale);
            localStorage.setItem('locale', locale);
            lpSyncLangButtons();
            lpSyncPlaceholders();

            // redirect:'manual' menahan browser mengikuti back() milik route lang.
            // Sesi tetap tersimpan, tapi halaman tidak ikut dirender ulang.
            fetch('{{ url('lang') }}/' + locale, {
                credentials: 'same-origin',
                redirect: 'manual',
                keepalive: true,
            }).catch(() => {});
        }

        function lpSyncLangButtons() {
            const current = document.documentElement.getAttribute('data-locale') || 'en';

            document.querySelectorAll('.lp-lang').forEach((group) => {
                const buttons = group.querySelectorAll('.lp-lang-btn');
                const thumb = group.querySelector('.lp-lang-thumb');
                let active = null;

                buttons.forEach((btn) => {
                    const on = btn.dataset.lang === current;
                    btn.classList.toggle('is-on', on);
                    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
                    if (on) active = btn;
                });

                if (thumb && active && buttons.length) {
                    thumb.style.width = active.offsetWidth + 'px';
                    thumb.style.transform = 'translateX(' + (active.offsetLeft - buttons[0].offsetLeft) + 'px)';
                }
            });
        }

        // Placeholder tidak bisa memuat dua varian sekaligus, jadi ditukar lewat atribut.
        function lpSyncPlaceholders() {
            const current = document.documentElement.getAttribute('data-locale') || 'en';
            document.querySelectorAll('.i18n-placeholder').forEach((el) => {
                const value = current === 'id' ? el.dataset.phId : el.dataset.phEn;
                if (value !== undefined) el.setAttribute('placeholder', value);
            });
        }

        // Skeleton gambar dilepas setelah berkasnya siap. Error pun melepasnya,
        // supaya tidak ada kotak berkedip yang tidak pernah selesai.
        function lpInitImageSkeletons() {
            document.querySelectorAll('img.sk-img:not([data-sk-bound])').forEach((img) => {
                img.dataset.skBound = '1';
                const frame = img.closest('.sk-frame');
                const done = () => {
                    img.classList.add('is-ready');
                    frame?.classList.add('is-ready');
                };

                if (img.complete && img.naturalWidth > 0) {
                    done();
                    return;
                }

                img.addEventListener('load', done, { once: true });
                img.addEventListener('error', done, { once: true });
            });
        }

        function lpInitReveal() {
            const items = document.querySelectorAll('[data-reveal]:not(.is-in)');
            if (!items.length) return;

            if (!lpMotionAllowed()) {
                items.forEach((el) => el.classList.add('is-in'));
                return;
            }

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-in');
                    obs.unobserve(entry.target);
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

            items.forEach((el) => observer.observe(el));
        }

        function lpInitCountUp() {
            const items = document.querySelectorAll('[data-countup]:not([data-counted])');
            if (!items.length) return;

            const run = (el) => {
                const target = parseFloat(el.dataset.countup);
                if (isNaN(target)) return;
                if (!lpMotionAllowed()) {
                    el.textContent = target;
                    return;
                }
                const start = performance.now();
                const step = (now) => {
                    const progress = Math.min((now - start) / 1100, 1);
                    el.textContent = Math.floor((1 - Math.pow(1 - progress, 3)) * target);
                    if (progress < 1) requestAnimationFrame(step);
                    else el.textContent = target;
                };
                requestAnimationFrame(step);
            };

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.dataset.counted = '1';
                    run(entry.target);
                    obs.unobserve(entry.target);
                });
            }, { threshold: 0.4 });

            items.forEach((el) => observer.observe(el));
        }

        function lpMoveNavPill(link) {
            const group = document.querySelector('.lp-nav-links');
            const pill = group?.querySelector('.lp-nav-pill');
            if (!group || !pill || !link) return;

            pill.style.width = link.offsetWidth + 'px';
            pill.style.transform = 'translateX(' + link.offsetLeft + 'px)';
            pill.classList.add('is-on');
        }

        function lpInitScrollSpy() {
            const sections = document.querySelectorAll('#lp-main section[id]');
            const links = document.querySelectorAll('.lp-nav-link, .lp-sheet-link');
            if (!sections.length || !links.length) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    links.forEach((link) => {
                        link.classList.toggle('is-active', link.getAttribute('href') === '#' + entry.target.id);
                    });
                    lpMoveNavPill(document.querySelector('.lp-nav-link.is-active'));
                });
            }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });

            sections.forEach((section) => observer.observe(section));

            const group = document.querySelector('.lp-nav-links');
            if (group && !group.dataset.bound) {
                group.dataset.bound = '1';
                group.addEventListener('pointerover', (event) => {
                    const link = event.target.closest('.lp-nav-link');
                    if (link) lpMoveNavPill(link);
                });
                group.addEventListener('pointerleave', () => {
                    lpMoveNavPill(document.querySelector('.lp-nav-link.is-active'));
                });
            }
        }

        function lpInitSheet() {
            const sheet = document.getElementById('lp-sheet');
            const openBtn = document.getElementById('lp-sheet-open');
            if (!sheet || !openBtn || sheet.dataset.bound) return;
            sheet.dataset.bound = '1';

            const panel = sheet.querySelector('.panel');

            const open = () => {
                sheet.classList.add('is-open');
                sheet.removeAttribute('inert');
                sheet.setAttribute('aria-hidden', 'false');
                openBtn.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
                lpSyncLangButtons();
                panel.querySelector('a, button')?.focus();
            };

            const close = () => {
                sheet.classList.remove('is-open');
                sheet.setAttribute('inert', '');
                sheet.setAttribute('aria-hidden', 'true');
                openBtn.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                openBtn.focus();
            };

            openBtn.addEventListener('click', open);
            document.getElementById('lp-sheet-close')?.addEventListener('click', close);
            document.getElementById('lp-sheet-backdrop')?.addEventListener('click', close);
            sheet.querySelectorAll('[data-sheet-link]').forEach((link) => link.addEventListener('click', close));
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && sheet.classList.contains('is-open')) close();
            });
        }

        // CV dibaca di dalam halaman. Sumber iframe baru dipasang saat modal
        // dibuka, jadi PDF tidak pernah diunduh untuk pengunjung yang tidak meminta.
        function lpInitCv() {
            const modal = document.getElementById('lp-cv');
            if (!modal || modal.dataset.bound) return;
            modal.dataset.bound = '1';

            const stage = document.getElementById('lp-cv-stage');
            const frame = document.getElementById('lp-cv-frame');
            const src = modal.dataset.cvSrc;
            let opener = null;

            frame.addEventListener('load', () => {
                if (frame.getAttribute('src')) stage.classList.add('is-ready');
            });

            const open = (event) => {
                event.preventDefault();
                opener = event.currentTarget;
                modal.classList.add('is-open');
                modal.removeAttribute('inert');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';

                // pdfViewerEnabled bernilai false di peramban yang justru akan
                // memaksa unduhan; di sana iframe cuma menghasilkan panel kosong.
                if (navigator.pdfViewerEnabled === false) {
                    stage.classList.add('is-fallback');
                } else if (!frame.getAttribute('src')) {
                    frame.setAttribute('src', src);
                }

                modal.querySelector('.lp-cv-acts [data-cv-close]')?.focus();
            };

            const close = () => {
                modal.classList.remove('is-open');
                modal.setAttribute('inert', '');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                opener?.focus();
            };

            document.querySelectorAll('[data-cv-open]').forEach((btn) => {
                if (btn.dataset.bound) return;
                btn.dataset.bound = '1';
                btn.addEventListener('click', open);
            });
            modal.querySelectorAll('[data-cv-close]').forEach((btn) => btn.addEventListener('click', close));
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
            });
        }

        function lpInitScrollWatch() {
            if (window.__lpScrollBound) return;
            window.__lpScrollBound = true;

            const bar = document.getElementById('lp-progress');
            const nav = document.getElementById('lp-nav');
            const top = document.getElementById('lp-top');
            const sheet = document.getElementById('lp-sheet');
            let last = window.scrollY;
            let ticking = false;

            const update = () => {
                const y = window.scrollY;

                if (nav) {
                    nav.classList.toggle('is-stuck', y > 12);
                    // Menyingkir hanya setelah jauh dari puncak, supaya gulir kecil
                    // di awal halaman tidak membuat nav berkedip.
                    nav.classList.toggle('is-away', y > 240 && y > last && !sheet?.classList.contains('is-open'));
                }

                if (top) top.classList.toggle('is-on', y > 700);

                if (bar) {
                    const scrollable = document.documentElement.scrollHeight - window.innerHeight;
                    bar.style.width = (scrollable > 0 ? (y / scrollable) * 100 : 0) + '%';
                }

                last = y;
                ticking = false;
            };

            window.addEventListener('scroll', () => {
                if (ticking) return;
                ticking = true;
                requestAnimationFrame(update);
            }, { passive: true });

            window.addEventListener('resize', () => {
                update();
                lpSyncLangButtons();
                lpMoveNavPill(document.querySelector('.lp-nav-link.is-active'));
            }, { passive: true });

            update();

            top?.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: lpMotionAllowed() ? 'smooth' : 'auto' });
            });
        }

        function lpBoot() {
            lpSyncThemeIcon();
            lpSyncLangButtons();
            lpSyncPlaceholders();
            lpInitImageSkeletons();
            lpInitReveal();
            lpInitCountUp();
            lpInitScrollSpy();
            lpInitSheet();
            lpInitCv();
            lpInitScrollWatch();

            document.querySelectorAll('.lp-theme-btn').forEach((btn) => {
                if (btn.dataset.bound) return;
                btn.dataset.bound = '1';
                btn.addEventListener('click', lpToggleTheme);
            });
        }

        document.addEventListener('livewire:navigated', lpBoot);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', lpBoot, { once: true });
        } else {
            lpBoot();
        }

        // Lebar thumb bahasa dan pil nav bergantung pada font yang sudah termuat.
        document.fonts?.ready.then(() => {
            lpSyncLangButtons();
            lpMoveNavPill(document.querySelector('.lp-nav-link.is-active'));
        });
    </script>

    @livewireScripts
</body>
</html>
