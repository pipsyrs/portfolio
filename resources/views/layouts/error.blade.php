@php
    $primaryColor = settings()->color();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <title>@yield('code', 'Error') | {{ config('app.name', 'Portfolio') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite / Tailwind -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: {{ $primaryColor }};
        }

        html, body {
            overflow-x: hidden;
            width: 100%;
            height: 100%;
            position: relative;
        }

        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            background-color: #fafbff;
            color: #1e293b;
            transition: background-color 0.4s cubic-bezier(0.4,0,0.2,1), color 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .dark body {
            background-color: #0c111b;
            color: #e2e8f0;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c7d2e0; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Glass panels */
        .glass-panel {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.04);
        }
        .dark .glass-panel {
            background: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        }

        /* Text gradient */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, #818cf8 50%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Vanta background container */
        #vanta-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
            pointer-events: none;
            opacity: 0.4;
        }

        /* Theme toggle */
        .theme-toggle-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        .theme-toggle-btn:hover {
            transform: rotate(15deg) scale(1.1);
        }

        /* Error code animation */
        .error-code {
            font-size: clamp(100px, 20vw, 200px);
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, var(--primary) 0%, #818cf8 40%, #c084fc 70%, var(--primary) 100%);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 4s ease infinite;
        }

        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Float animation */
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Pulse glow */
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }

        /* Particle dots */
        .particle {
            position: absolute;
            border-radius: 50%;
            animation: particle-float linear infinite;
            opacity: 0;
        }
        @keyframes particle-float {
            0% { opacity: 0; transform: translateY(0) scale(0); }
            10% { opacity: 0.6; transform: scale(1); }
            90% { opacity: 0.6; }
            100% { opacity: 0; transform: translateY(-800px) scale(0.5); }
        }
    </style>
</head>
<body class="antialiased selection:bg-indigo-500/30 selection:text-indigo-900 dark:selection:bg-indigo-400/30 dark:selection:text-white">

    <!-- Vanta.js Background -->
    <div id="vanta-bg"></div>

    <!-- Floating Particles -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none" id="particles-container"></div>

    <!-- Theme Toggle (top right) -->
    <div class="fixed top-6 right-6 z-50">
        <button id="theme-toggle-error" class="theme-toggle-btn bg-white/80 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 hover:text-amber-500 dark:hover:text-amber-400 border border-slate-200/50 dark:border-slate-700/30 shadow-lg focus:outline-none">
            <i class="fas fa-moon text-sm" id="theme-icon-error"></i>
        </button>
    </div>

    <!-- Main Content -->
    <main class="relative z-10 min-h-screen flex items-center justify-center px-6 py-12">
        <div class="text-center max-w-2xl mx-auto">

            <!-- Glowing Orb Behind Code -->
            <div class="relative inline-block mb-2">
                <div class="absolute inset-0 blur-[80px] pulse-glow" style="background: linear-gradient(135deg, var(--primary), #818cf8); border-radius: 50%;"></div>
                <div class="error-code relative float-animation">
                    @yield('code', '???')
                </div>
            </div>

            <!-- Error Icon -->
            <div class="mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/60 dark:bg-slate-800/40 border border-slate-200/50 dark:border-slate-700/30 shadow-lg backdrop-blur-xl">
                    <i class="@yield('icon', 'fas fa-exclamation-triangle') text-2xl" style="color: var(--primary);"></i>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-white mb-4">
                @yield('title', __('Something Went Wrong'))
            </h1>

            <!-- Message -->
            <p class="text-slate-500 dark:text-slate-400 text-base md:text-lg max-w-lg mx-auto mb-10 leading-relaxed">
                @yield('message', __('An unexpected error occurred. Please try again later.'))
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ url('/') }}" class="group inline-flex items-center gap-2 px-7 py-3.5 text-white font-bold rounded-xl shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl" style="background: linear-gradient(135deg, var(--primary), #818cf8); box-shadow: 0 8px 30px rgba(56, 189, 248, 0.2);">
                    <i class="fas fa-home text-sm transition-transform group-hover:-translate-x-0.5"></i>
                    {{ __('Back to Home') }}
                </a>
                <button onclick="history.back()" class="inline-flex items-center gap-2 px-7 py-3.5 font-bold rounded-xl transition-all duration-300 hover:-translate-y-0.5 glass-panel text-slate-700 dark:text-slate-200 hover:shadow-lg">
                    <i class="fas fa-arrow-left text-sm"></i>
                    {{ __('Go Back') }}
                </button>
            </div>

            <!-- Fun Decoration -->
            <div class="mt-16 flex items-center justify-center gap-2 text-slate-300 dark:text-slate-700">
                <div class="w-12 h-px bg-current"></div>
                <i class="fas fa-code text-xs"></i>
                <div class="w-12 h-px bg-current"></div>
            </div>
            <p class="mt-4 text-xs text-slate-400 dark:text-slate-600 font-mono tracking-wider">
                PORT<span style="color: var(--primary);">FOLIO</span>
            </p>
        </div>
    </main>

    <!-- Vanta.js Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.net.min.js"></script>

    <script>
        // Initialize Vanta
        (function() {
            const isDark = document.documentElement.classList.contains('dark');
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#38bdf8';

            function hexToInt(hex) {
                return parseInt(hex.replace('#', ''), 16);
            }

            try {
                VANTA.NET({
                    el: '#vanta-bg',
                    mouseControls: true,
                    touchControls: true,
                    gyroControls: false,
                    minHeight: 200.00,
                    minWidth: 200.00,
                    scale: 1.00,
                    scaleMobile: 1.00,
                    color: hexToInt(primaryColor),
                    backgroundColor: isDark ? 0x0c111b : 0xfafbff,
                    points: 8.00,
                    maxDistance: 22.00,
                    spacing: 18.00,
                    showDots: true,
                });
            } catch(e) {}
        })();

        // Theme Toggle
        (function() {
            const btn = document.getElementById('theme-toggle-error');
            const icon = document.getElementById('theme-icon-error');

            function syncIcon() {
                const isDark = document.documentElement.classList.contains('dark');
                if (isDark) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }

            syncIcon();

            if (btn) {
                btn.addEventListener('click', function() {
                    document.documentElement.classList.toggle('dark');
                    localStorage.setItem('color-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                    syncIcon();
                    // Reload vanta with new background
                    location.reload();
                });
            }
        })();

        // Generate floating particles
        (function() {
            const container = document.getElementById('particles-container');
            if (!container) return;

            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#38bdf8';
            const colors = [primaryColor, '#818cf8', '#c084fc', '#f472b6'];

            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const size = Math.random() * 6 + 2;
                const color = colors[Math.floor(Math.random() * colors.length)];
                particle.style.cssText = `
                    width: ${size}px;
                    height: ${size}px;
                    background: ${color};
                    left: ${Math.random() * 100}%;
                    bottom: -10px;
                    animation-duration: ${Math.random() * 10 + 8}s;
                    animation-delay: ${Math.random() * 10}s;
                `;
                container.appendChild(particle);
            }
        })();
    </script>
</body>
</html>
