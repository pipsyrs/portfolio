{{--
    Sumber tunggal token desain: dipakai landing page dan dashboard supaya
    keduanya tidak pernah lepas sinkron. $primary berasal dari Setting::current().
--}}
@props(['primary' => '#38bdf8'])
<style>
    :root {
        --primary: {{ $primary }};
        --ink: #1a1f2b;
        --ink-soft: #5b6472;
        --hairline: #e2e5eb;
        --surface: #ffffff;
        --surface-alt: #f2f4f8;
        --glass-bg: rgba(255, 255, 255, 0.62);
        --glass-border: rgba(15, 23, 42, 0.09);
        --font-mono: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;

        --success: #10b981;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #3b82f6;
    }

    .dark {
        --ink: #e7ecf3;
        --ink-soft: #8b95a8;
        --hairline: #232a38;
        --surface: #0a0e16;
        --surface-alt: #10151f;
        --glass-bg: rgba(255, 255, 255, 0.045);
        --glass-border: rgba(255, 255, 255, 0.09);
    }

    html, body { overflow-x: hidden; width: 100%; }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background-color: var(--surface);
        color: var(--ink);
        transition: background-color 0.25s ease, color 0.25s ease;
    }

    /* Tekstur grid "blueprint" di belakang segalanya — fixed dan di-mask radial
       supaya terbaca sebagai kedalaman ambient, bukan noise. */
    #bg-grid {
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        background-image:
            linear-gradient(color-mix(in srgb, var(--ink) 6%, transparent) 1px, transparent 1px),
            linear-gradient(90deg, color-mix(in srgb, var(--ink) 6%, transparent) 1px, transparent 1px);
        background-size: 44px 44px;
        -webkit-mask-image: radial-gradient(ellipse 75% 55% at 50% 0%, black, transparent 75%);
        mask-image: radial-gradient(ellipse 75% 55% at 50% 0%, black, transparent 75%);
    }

    /* Kedua varian bahasa dirender server-side, CSS hanya menyembunyikan salah
       satu — pergantian instan tanpa render ulang. Lihat bt() di helper.php. */
    html[data-locale="id"] .i18n-en { display: none; }
    html:not([data-locale="id"]) .i18n-id { display: none; }

    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #d4d4d4; border-radius: 99px; }
    ::-webkit-scrollbar-thumb:hover { background: #b5b5b5; }
    .dark ::-webkit-scrollbar-thumb { background: #3a3a3a; }
    .dark ::-webkit-scrollbar-thumb:hover { background: #4d4d4d; }

    /* Sistem kartu glass — permukaan translusen + blur supaya grid terbaca
       tembus, hairline border yang berubah jadi glow aksen saat hover. */
    .card {
        background-color: var(--glass-bg);
        backdrop-filter: blur(18px) saturate(160%);
        -webkit-backdrop-filter: blur(18px) saturate(160%);
        border: 1px solid var(--glass-border);
        transition: box-shadow 0.25s ease, transform 0.25s ease, border-color 0.25s ease;
    }
    .card-hover:hover {
        border-color: color-mix(in srgb, var(--primary) 40%, var(--glass-border));
        box-shadow: 0 10px 30px color-mix(in srgb, var(--primary) 12%, transparent), 0 4px 14px rgba(0, 0, 0, 0.06);
        transform: translateY(-3px);
    }
    .dark .card-hover:hover {
        box-shadow: 0 0 0 1px color-mix(in srgb, var(--primary) 30%, transparent), 0 10px 34px color-mix(in srgb, var(--primary) 18%, transparent);
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary);
        font-family: var(--font-mono);
        font-weight: 600;
        font-size: 0.8125rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .eyebrow .section-num { color: var(--ink-soft); opacity: 0.7; }
    .eyebrow .section-num::after { content: '/'; margin-left: 0.4em; opacity: 0.6; }

    .mono { font-family: var(--font-mono); }

    .btn-primary {
        background-color: var(--primary);
        color: #fff;
        font-weight: 600;
        transition: filter 0.2s ease, transform 0.2s ease;
    }
    .btn-primary:hover { filter: brightness(0.92); transform: translateY(-1px); }
    .btn-primary:disabled { opacity: 0.55; cursor: not-allowed; transform: none; filter: none; }

    .btn-secondary {
        background-color: transparent;
        border: 1px solid var(--hairline);
        color: var(--ink);
        font-weight: 600;
        transition: border-color 0.2s ease, transform 0.2s ease;
    }
    .btn-secondary:hover { border-color: var(--ink-soft); transform: translateY(-1px); }
    .btn-secondary:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }

    .theme-toggle-btn {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background-color: var(--surface-alt);
        border: 1px solid var(--hairline);
        color: var(--ink-soft);
        transition: color 0.2s ease, border-color 0.2s ease;
    }
    .theme-toggle-btn:hover { color: #f59e0b; }

    /* Tombol yang mengikuti kursor dalam batasnya sendiri — JS menggerakkan
       transform, CSS hanya memberi easing. */
    .magnetic { transition: transform 0.2s cubic-bezier(0.2, 0.8, 0.2, 1); will-change: transform; }

    /* Konten HTML dari rich editor (about, project, career) — satu gaya bersama
       supaya teks terformat tampak native, bukan default browser. */
    .prose-content { color: var(--ink-soft); line-height: 1.75; }
    .prose-content > *:first-child { margin-top: 0; }
    .prose-content > *:last-child { margin-bottom: 0; }
    .prose-content p { margin: 0 0 0.85em; }
    .prose-content strong { color: var(--ink); font-weight: 600; }
    .prose-content a { color: var(--primary); text-decoration: underline; text-underline-offset: 2px; }
    .prose-content ul, .prose-content ol { margin: 0.5em 0 0.85em 1.25em; }
    .prose-content ul { list-style: disc; }
    .prose-content ol { list-style: decimal; }
    .prose-content li { margin-bottom: 0.35em; }
    .prose-content blockquote { border-left: 3px solid var(--primary); padding-left: 1em; margin: 0.85em 0; font-style: italic; }
    .prose-content h2, .prose-content h3 { color: var(--ink); font-weight: 700; margin: 0.9em 0 0.4em; }
    .prose-content h2 { font-size: 1.3em; }
    .prose-content h3 { font-size: 1.1em; }
    .prose-content code { background-color: var(--surface-alt); padding: 0.15em 0.4em; border-radius: 0.35em; font-size: 0.9em; }

    /* Memudarkan preview rich-text yang terpotong, bukan memotongnya mendadak.
       Masking opacity konten sendiri supaya menyatu di background apa pun. */
    .fade-clip {
        overflow: hidden;
        -webkit-mask-image: linear-gradient(to bottom, black 65%, transparent 100%);
        mask-image: linear-gradient(to bottom, black 65%, transparent 100%);
    }

    [x-cloak] { display: none !important; }
</style>
