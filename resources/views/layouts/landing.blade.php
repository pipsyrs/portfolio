@php
    $user = \App\Models\User::owner();
    $primaryColor = settings()->color();
    $currentLocale = app()->getLocale();
    $seoKeywords = settings('seo_keywords') ?: $user?->keywords;
    $seoImage = safe_image_url(settings('seo_og_image') ?: ($user->foto ?? 'images/seo-banner.jpg'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" data-locale="{{ $currentLocale }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script>
        // Prevent FOUC - apply theme + language before first paint.
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

    <!-- Primary Meta Tags -->
    <meta name="title" content="{{ $user->name ?? 'Portfolio' }} | {{ $user->specialis ?? 'Fullstack Web Developer' }}">
    <meta name="description" content="{{ $user->headline ?? 'Portfolio of John Doe, a passionate Fullstack Web Developer specializing in Laravel, Vue.js, and Tailwind CSS. Building robust, scalable, and beautifully designed web applications.' }}">
    <meta name="keywords" content="{{ $seoKeywords }}">
    <meta name="author" content="{{ $user->name }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $user->name ?? config('app.name', 'Portfolio') }} | {{ $user->specialis ?? 'Fullstack Web Developer' }}">
    <meta property="og:description" content="{{ $user->headline ?? 'Portfolio of John Doe, a passionate Fullstack Web Developer specializing in Laravel, Vue.js, and Tailwind CSS.' }}">
    <meta property="og:image" content="{{ $seoImage }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $user->name ?? config('app.name', 'Portfolio') }} | {{ $user->specialis ?? 'Fullstack Web Developer' }}">
    <meta property="twitter:description" content="{{ $user->headline ?? 'Portfolio of John Doe, a passionate Fullstack Web Developer specializing in Laravel, Vue.js, and Tailwind CSS.' }}">
    <meta property="twitter:image" content="{{ $seoImage }}">

    <!-- Google Fonts: Inter (UI) + JetBrains Mono (tech/security accents) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AOS CSS (subtle scroll reveal only) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Vite / Tailwind -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    @include('partials.design-tokens', ['primary' => $primaryColor])

    <style>
        section { position: relative; z-index: 1; }

        /* Nav */
        #navbar {
            background-color: color-mix(in srgb, var(--surface) 78%, transparent);
            backdrop-filter: blur(14px) saturate(160%);
            -webkit-backdrop-filter: blur(14px) saturate(160%);
            border-bottom: 1px solid transparent;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }
        #navbar.is-scrolled { border-bottom-color: var(--hairline); }

        .nav-link {
            position: relative;
            color: var(--ink-soft);
        }
        .nav-link:hover { color: var(--ink); }
        .nav-link::after {
            content: '';
            position: absolute;
            left: 0.75rem; right: 0.75rem; bottom: 0.35rem;
            height: 2px;
            background: var(--primary);
            border-radius: 99px;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.25s ease;
        }
        .nav-link:hover::after { transform: scaleX(1); }

        /* Scrollspy active nav link (persistent, on top of the hover state) */
        .nav-link.active { color: var(--ink); }
        .nav-link.active::after { transform: scaleX(1); }

        .lang-toggle {
            background-color: var(--surface-alt);
            border: 1px solid var(--hairline);
        }
        .lang-btn {
            font-weight: 700;
            font-size: 0.75rem;
            color: var(--ink-soft);
            transition: color 0.2s ease, background-color 0.2s ease;
        }
        .lang-btn-active {
            color: #fff;
            background-color: var(--primary);
        }

        .project-filter-chip {
            background-color: var(--surface);
            border: 1px solid var(--hairline);
            color: var(--ink-soft);
            transition: all 0.2s ease;
        }
        .project-filter-chip:hover { border-color: var(--ink-soft); color: var(--ink); }
        .project-filter-chip.active {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        /* Scroll progress bar */
        #scroll-progress {
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            width: 0%;
            background-color: var(--primary);
            z-index: 70;
        }
    </style>
</head>
<body class="antialiased">

    <div id="bg-grid" aria-hidden="true"></div>
    <div id="scroll-progress"></div>

    <!-- Navbar -->
    <nav class="fixed w-full z-50" id="navbar">
        <div class="container mx-auto px-6 md:px-12 lg:px-24">
            <div class="flex justify-between items-center h-[68px]">
                <a href="#" class="text-lg font-bold tracking-tight" style="color: var(--ink);">
                    PORT<span style="color: var(--primary);">FOLIO</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center gap-1">
                    <a href="#hero" class="nav-link text-sm font-medium px-3 py-2 rounded-lg">{!! bt('Home') !!}</a>
                    <a href="#about" class="nav-link text-sm font-medium px-3 py-2 rounded-lg">{!! bt('About') !!}</a>
                    <a href="#tech" class="nav-link text-sm font-medium px-3 py-2 rounded-lg">{!! bt('Skills') !!}</a>
                    <a href="#specialis" class="nav-link text-sm font-medium px-3 py-2 rounded-lg">{!! bt('Specialties') !!}</a>
                    <a href="#careers" class="nav-link text-sm font-medium px-3 py-2 rounded-lg">{!! bt('Careers') !!}</a>
                    <a href="#certifications" class="nav-link text-sm font-medium px-3 py-2 rounded-lg">{!! bt('Certifications') !!}</a>
                    <a href="#projects" class="nav-link text-sm font-medium px-3 py-2 rounded-lg">{!! bt('Projects') !!}</a>
                    <a href="#contact" class="nav-link text-sm font-medium px-3 py-2 rounded-lg">{!! bt('Contact') !!}</a>
                </div>

                <div class="hidden lg:flex items-center gap-3">
                    <div class="lang-toggle flex items-center rounded-xl p-1">
                        <button type="button" class="lang-btn lang-btn-desktop rounded-lg px-3 py-1.5" data-lang="id" onclick="switchLocale('id')">ID</button>
                        <button type="button" class="lang-btn lang-btn-desktop rounded-lg px-3 py-1.5" data-lang="en" onclick="switchLocale('en')">EN</button>
                    </div>

                    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark mode">
                        <i class="fas fa-moon text-sm theme-icon" id="theme-icon-desktop"></i>
                    </button>

                    <a href="{{ route('view.cv') }}" target="_blank"
                        class="magnetic btn-primary flex items-center gap-2 px-5 py-2.5 rounded-xl">
                        <i class="fas fa-file-pdf text-sm"></i>
                        <span class="text-sm">{!! bt('View CV') !!}</span>
                    </a>
                </div>

                <!-- Mobile Actions -->
                <div class="lg:hidden flex items-center space-x-3">
                    <button id="theme-toggle-mobile" class="theme-toggle-btn w-9 h-9" aria-label="Toggle dark mode">
                        <i class="fas fa-moon text-sm theme-icon" id="theme-icon-mobile"></i>
                    </button>
                    <button class="theme-toggle-btn w-9 h-9" id="mobile-menu-btn" aria-label="Open menu">
                        <i class="fas fa-bars text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="fixed inset-0 z-[60] hidden flex-col items-center justify-center space-y-4 transition-all" id="mobile-menu" style="background-color: var(--surface);">
        <button class="absolute top-6 right-6 w-10 h-10 rounded-xl flex items-center justify-center z-50 theme-toggle-btn" id="mobile-close-btn" aria-label="Close menu">
            <i class="fas fa-times"></i>
        </button>

        <!-- Lang Toggle Mobile -->
        <div class="lang-toggle flex items-center rounded-xl p-1.5 mb-4">
            <button type="button" class="lang-btn lang-btn-mobile rounded-lg px-5 py-2 text-sm" data-lang="id" onclick="switchLocale('id')">ID</button>
            <button type="button" class="lang-btn lang-btn-mobile rounded-lg px-5 py-2 text-sm" data-lang="en" onclick="switchLocale('en')">EN</button>
        </div>

        <a href="#hero" class="text-lg font-medium mobile-link" style="color: var(--ink);">{!! bt('Home') !!}</a>
        <a href="#about" class="text-lg font-medium mobile-link" style="color: var(--ink);">{!! bt('About') !!}</a>
        <a href="#tech" class="text-lg font-medium mobile-link" style="color: var(--ink);">{!! bt('Skills') !!}</a>
        <a href="#specialis" class="text-lg font-medium mobile-link" style="color: var(--ink);">{!! bt('Specialties') !!}</a>
        <a href="#careers" class="text-lg font-medium mobile-link" style="color: var(--ink);">{!! bt('Careers') !!}</a>
        <a href="#certifications" class="text-lg font-medium mobile-link" style="color: var(--ink);">{!! bt('Certifications') !!}</a>
        <a href="#projects" class="text-lg font-medium mobile-link" style="color: var(--ink);">{!! bt('Projects') !!}</a>
        <a href="#contact" class="text-lg font-medium mobile-link" style="color: var(--ink);">{!! bt('Contact') !!}</a>

        <a href="{{ route('view.cv') }}" target="_blank"
            class="btn-primary flex items-center gap-2 px-8 py-3.5 mt-3 rounded-xl">
            <i class="fas fa-file-pdf text-sm"></i>
            <span>{!! bt('View CV') !!}</span>
        </a>
    </div>

    <main>
        @yield('content')
    </main>

    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script data-navigate-once>
        // Inisialisasi AOS
        function initAOS() {
            AOS.init({
                once: true,
                offset: 40,
                duration: 500,
                easing: 'ease-out-cubic',
            });
            setTimeout(() => AOS.refresh(), 100);
        }

        // Sync icon toggle (satu icon bergantian)
        function syncThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            const iconD = document.getElementById('theme-icon-desktop');
            const iconM = document.getElementById('theme-icon-mobile');

            [iconD, iconM].forEach(icon => {
                if (!icon) return;
                if (isDark) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            });
        }

        function handleThemeToggle() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('color-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            syncThemeIcon();
        }

        // Language: both EN/ID strings are already in the DOM (see bt()/bt_dynamic()),
        // so switching is a pure CSS attribute flip — no reload, no flicker.
        function switchLocale(locale) {
            if (locale !== 'en' && locale !== 'id') return;
            document.documentElement.setAttribute('data-locale', locale);
            document.documentElement.setAttribute('lang', locale);
            localStorage.setItem('locale', locale);
            syncLangButtons();
            syncPlaceholders();

            // Sync the session server-side in the background (best effort, no UI
            // impact) so a later real page load / the CV route / SEO crawlers see
            // a consistent locale too.
            fetch('{{ url('lang') }}/' + locale, { credentials: 'same-origin' }).catch(() => {});
        }

        function syncLangButtons() {
            const current = document.documentElement.getAttribute('data-locale') || 'en';
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.classList.toggle('lang-btn-active', btn.dataset.lang === current);
            });
        }

        // Inputs can't hold two visible language variants like text does, so their
        // placeholder is swapped directly via data-ph-en / data-ph-id attributes.
        function syncPlaceholders() {
            const current = document.documentElement.getAttribute('data-locale') || 'en';
            document.querySelectorAll('.i18n-placeholder').forEach(el => {
                const value = current === 'id' ? el.dataset.phId : el.dataset.phEn;
                if (value !== undefined) el.setAttribute('placeholder', value);
            });
        }

        document.addEventListener('livewire:navigated', () => {
            initAOS();
            syncThemeIcon();
            syncLangButtons();
            syncPlaceholders();
            initScrollProgress();
            initScrollSpy();
            initMagnetic();
            initHeroSpotlight();
            initCountUp();

            // Mobile menu
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const closeBtn = document.getElementById('mobile-close-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileLinks = document.querySelectorAll('.mobile-link');

            function toggleMenu() {
                if (mobileMenu) {
                    mobileMenu.classList.toggle('hidden');
                    mobileMenu.classList.toggle('flex');
                    document.body.classList.toggle('overflow-hidden');
                }
            }

            if (mobileBtn) {
                mobileBtn.removeEventListener('click', toggleMenu);
                mobileBtn.addEventListener('click', toggleMenu);
            }
            if (closeBtn) {
                closeBtn.removeEventListener('click', toggleMenu);
                closeBtn.addEventListener('click', toggleMenu);
            }
            mobileLinks.forEach(link => {
                link.removeEventListener('click', toggleMenu);
                link.addEventListener('click', toggleMenu);
            });

            // Bind theme toggle buttons
            const toggleD = document.getElementById('theme-toggle');
            const toggleM = document.getElementById('theme-toggle-mobile');

            if (toggleD) {
                toggleD.removeEventListener('click', handleThemeToggle);
                toggleD.addEventListener('click', handleThemeToggle);
            }
            if (toggleM) {
                toggleM.removeEventListener('click', handleThemeToggle);
                toggleM.addEventListener('click', handleThemeToggle);
            }
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (!nav) return;
            nav.classList.toggle('is-scrolled', window.scrollY > 20);
        });

        // Thin progress bar reflecting how far down the page the visitor is.
        function initScrollProgress() {
            const bar = document.getElementById('scroll-progress');
            if (!bar) return;
            const update = () => {
                const scrollable = document.documentElement.scrollHeight - window.innerHeight;
                bar.style.width = (scrollable > 0 ? (window.scrollY / scrollable) * 100 : 0) + '%';
            };
            window.addEventListener('scroll', update, { passive: true });
            update();
        }

        // Highlights the nav link matching whichever section is currently in view.
        function initScrollSpy() {
            if (window.__scrollSpyInit) return;
            window.__scrollSpyInit = true;

            const sections = document.querySelectorAll('main section[id]');
            const links = document.querySelectorAll('.nav-link');
            if (!sections.length || !links.length) return;

            const setActive = (id) => {
                links.forEach(link => link.classList.toggle('active', link.getAttribute('href') === '#' + id));
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) setActive(entry.target.id);
                });
            }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });

            sections.forEach(section => observer.observe(section));
        }

        // Buttons that gently follow the cursor within their own bounds on hover.
        function initMagnetic() {
            document.querySelectorAll('.magnetic').forEach(el => {
                if (el.dataset.magneticBound) return;
                el.dataset.magneticBound = '1';

                el.addEventListener('mousemove', (e) => {
                    const rect = el.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    el.style.transform = `translate(${x * 0.25}px, ${y * 0.35}px)`;
                });
                el.addEventListener('mouseleave', () => {
                    el.style.transform = 'translate(0, 0)';
                });
            });
        }

        // Soft radial glow in the hero that follows the cursor — a quiet bit of
        // depth instead of a distracting animated background.
        function initHeroSpotlight() {
            const hero = document.getElementById('hero');
            const spotlight = document.getElementById('hero-spotlight');
            if (!hero || !spotlight || hero.dataset.spotlightBound) return;
            hero.dataset.spotlightBound = '1';

            hero.addEventListener('mousemove', (e) => {
                const rect = hero.getBoundingClientRect();
                spotlight.style.setProperty('--x', (e.clientX - rect.left) + 'px');
                spotlight.style.setProperty('--y', (e.clientY - rect.top) + 'px');
            });
        }

        // Counts numeric stats up from 0 once they scroll into view.
        function initCountUp() {
            const els = document.querySelectorAll('[data-countup]');
            if (!els.length || window.__countUpInit) return;
            window.__countUpInit = true;

            const animate = (el) => {
                const target = parseFloat(el.dataset.countup);
                if (isNaN(target)) return;
                const duration = 1100;
                const start = performance.now();
                const step = (now) => {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.floor(eased * target);
                    if (progress < 1) requestAnimationFrame(step);
                    else el.textContent = target;
                };
                requestAnimationFrame(step);
            };

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animate(entry.target);
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.4 });

            els.forEach(el => observer.observe(el));
        }
    </script>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
