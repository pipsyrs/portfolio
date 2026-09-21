<section id="hero" class="lp-section lp-section--open" style="padding-block-start: calc(var(--nav-h) + clamp(2.5rem, 7vw, 6rem));">
    <div class="lp-shell">
        <div class="grid lg:grid-cols-12 gap-x-12 gap-y-14 lg:items-center">

            <div class="lg:col-span-7">
                <p class="hero-status" data-reveal>
                    <span class="hero-status-dot" aria-hidden="true"></span>
                    <span class="i18n-en">Open for freelance and full time work</span><span class="i18n-id">Terbuka untuk kerja lepas dan penuh waktu</span>
                </p>

                <h1 class="lp-display mt-7" data-reveal style="--reveal-delay:60ms">
                    {{ $user->name ?? 'Portfolio' }}
                </h1>

                <p class="hero-role mt-4" data-reveal style="--reveal-delay:110ms">
                    <span class="lp-grad">{{ $user->specialis ?? 'Web Developer' }}</span>
                </p>

                @if ($user->headline)
                    <p class="lp-lead mt-6" data-reveal style="--reveal-delay:150ms">
                        {!! bt_dynamic($user->headline) !!}
                    </p>
                @endif

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 mt-10" data-reveal style="--reveal-delay:200ms">
                    <a href="#projects" class="lp-btn lp-btn-primary">
                        {!! bt('See what I have built') !!}
                        <i class="fas fa-arrow-down text-xs" aria-hidden="true"></i>
                    </a>
                    @if (settings('github_link'))
                        <a href="{{ settings('github_link') }}" target="_blank" rel="noopener noreferrer" class="lp-btn lp-btn-ghost">
                            <i class="fab fa-github" aria-hidden="true"></i>
                            {!! bt('Code on GitHub') !!}
                        </a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5" data-reveal style="--reveal-delay:120ms">
                {{-- Bingkai memegang rasio 4:5 sejak awal; skeleton di dalamnya
                     baru dilepas setelah potretnya benar-benar termuat. --}}
                <div class="hero-portrait mx-auto lg:mx-0">
                    <div class="hero-portrait-frame sk-frame">
                        <img src="{{ safe_image_url($user->foto) }}"
                             alt="{{ $user->name ?? 'Portrait' }}"
                             width="640" height="800"
                             fetchpriority="high"
                             class="sk-img">
                    </div>
                </div>
            </div>
        </div>

        @php
            $facts = collect([
                ['value' => (int) $user->experience, 'en' => 'Years building for the web', 'id' => 'Tahun membangun untuk web'],
                ['value' => (int) $projectsCount, 'en' => 'Projects shipped', 'id' => 'Proyek dikerjakan'],
                ['value' => (int) $certificationsCount, 'en' => 'Certifications held', 'id' => 'Sertifikasi dimiliki'],
            ])->filter(fn (array $fact) => $fact['value'] > 0);
        @endphp

        @if ($facts->isNotEmpty())
            <dl class="hero-facts" data-reveal>
                @foreach ($facts as $fact)
                    <div class="hero-fact">
                        <dd class="hero-fact-value">
                            <span data-countup="{{ $fact['value'] }}">0</span>
                            <span class="hero-fact-plus" aria-hidden="true">+</span>
                        </dd>
                        <dt class="lp-meta mt-2">
                            <span class="i18n-en">{{ $fact['en'] }}</span><span class="i18n-id">{{ $fact['id'] }}</span>
                        </dt>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</section>

<style>
    .hero-status {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.45rem 0.95rem;
        border: 1px solid var(--hairline);
        border-radius: 999px;
        background-color: var(--surface-alt);
        box-shadow: var(--inner-lift);
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--ink-soft);
    }
    .hero-status-dot {
        position: relative;
        inline-size: 7px;
        block-size: 7px;
        flex: none;
        border-radius: 999px;
        background-color: var(--success);
    }
    /* Denyut menandai status yang hidup, bukan label statis. */
    .hero-status-dot::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background-color: var(--success);
        animation: hero-pulse 2s var(--ease-out) infinite;
    }
    @keyframes hero-pulse {
        0% { transform: scale(1); opacity: 0.6; }
        70%, 100% { transform: scale(3); opacity: 0; }
    }

    .hero-role {
        font-size: clamp(1.35rem, 3.4vw, 2.25rem);
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: -0.03em;
    }

    .hero-portrait { max-inline-size: 22rem; }
    .hero-portrait-frame {
        position: relative;
        border-radius: var(--r-slab);
        border: 1px solid var(--hairline);
        background-color: var(--surface-alt);
        padding: 6px;
        box-shadow: var(--shadow-md), var(--inner-lift);
        aspect-ratio: 4 / 5;
    }
    .hero-portrait-frame img {
        inline-size: 100%;
        block-size: 100%;
        object-fit: cover;
        border-radius: calc(var(--r-slab) - 6px);
    }
    /* Lingkaran aksen samar di belakang potret memberi kedalaman tanpa menambah elemen. */
    .hero-portrait::before {
        content: '';
        position: absolute;
        inset: -12% -8% 20% -8%;
        border-radius: 999px;
        background-image: radial-gradient(closest-side, color-mix(in srgb, var(--primary) 22%, transparent), transparent);
        filter: blur(24px);
        z-index: -1;
    }
    .hero-portrait { position: relative; }

    @media (min-width: 1024px) {
        .hero-portrait { max-inline-size: none; }
    }

    .hero-facts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 13rem), 1fr));
        gap: 1px;
        margin-block-start: clamp(3.5rem, 8vw, 6rem);
        background-color: var(--rule);
        border: 1px solid var(--rule);
        border-radius: var(--r-card);
        overflow: hidden;
    }
    .hero-fact {
        padding: clamp(1.25rem, 3vw, 1.85rem);
        background-color: var(--surface);
    }
    .hero-fact-value {
        display: flex;
        align-items: baseline;
        gap: 0.1rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: -0.04em;
        color: var(--ink);
        font-size: clamp(2.25rem, 5vw, 3.25rem);
    }
    .hero-fact-plus { color: var(--accent-text); font-size: 0.55em; }

    @media (prefers-reduced-motion: reduce) {
        .hero-status-dot::after { animation: none; }
    }
</style>
