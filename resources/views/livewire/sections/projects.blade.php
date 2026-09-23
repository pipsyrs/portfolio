@php
    $techNames = $projects->flatMap->techStacks->pluck('name')->unique()->sort()->values();
@endphp

<section id="projects" class="lp-section lp-section--open">
    <div class="lp-shell">
        <div class="flex flex-wrap items-end justify-between gap-5">
            <div>
                <p class="lp-slug" data-reveal>
                    <span class="idx">07</span>
                    <span class="i18n-en">Projects</span><span class="i18n-id">Proyek</span>
                </p>
                <h2 class="lp-h2 mt-6" data-reveal style="--reveal-delay:60ms">
                    <span class="i18n-en">Things I have shipped</span><span class="i18n-id">Yang sudah saya kerjakan</span>
                </h2>
            </div>
            @if ($projects->count() > 0)
                <p class="lp-meta" data-reveal>{{ $projects->count() }}
                    <span class="i18n-en">in the list</span><span class="i18n-id">dalam daftar</span>
                </p>
            @endif
        </div>

        @if ($projects->count() > 0)
            @if ($techNames->count() > 1)
                <div class="project-filters" id="project-filters" data-reveal style="--reveal-delay:100ms">
                    <button type="button" class="lp-chip is-on" data-filter="all">
                        <span class="i18n-en">Everything</span><span class="i18n-id">Semua</span>
                    </button>
                    @foreach ($techNames as $techName)
                        <button type="button" class="lp-chip" data-filter="{{ Str::slug($techName) }}">{{ $techName }}</button>
                    @endforeach
                </div>
            @endif

            <div class="project-grid" id="project-grid">
                @foreach ($projects as $index => $project)
                    <article class="project-card lp-card lp-card-int {{ $index === 0 ? 'is-wide' : '' }}"
                             data-tech="{{ $project->techStacks->pluck('name')->map(fn ($name) => Str::slug($name))->implode(' ') }}"
                             data-reveal style="--reveal-delay:{{ min($index, 5) * 45 }}ms">

                        <div class="project-shot sk-frame">
                            <img src="{{ safe_image_url($project->image) }}" alt="{{ $project->name }}" loading="lazy" class="sk-img">
                        </div>

                        <div class="project-body">
                            <h3 class="lp-h3">{!! bt_dynamic($project->name) !!}</h3>

                            @if ($project->description)
                                <div class="prose-content fade-clip project-desc">
                                    {!! bt_dynamic($project->description, html: true) !!}
                                </div>
                            @endif

                            @if ($project->techStacks->count())
                                <div class="project-tags">
                                    @foreach ($project->techStacks as $tech)
                                        <span class="lp-tag">
                                            <i class="{{ $tech->icon }}" aria-hidden="true"></i>
                                            {{ $tech->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if ($project->url || $project->github_link)
                                <div class="project-links">
                                    @if ($project->url)
                                        <a href="{{ $project->url }}" target="_blank" rel="noopener noreferrer">
                                            <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                            {!! bt('Open the site') !!}
                                        </a>
                                    @endif
                                    @if ($project->github_link)
                                        <a href="{{ $project->github_link }}" target="_blank" rel="noopener noreferrer">
                                            <i class="fab fa-github" aria-hidden="true"></i>
                                            {!! bt('Source') !!}
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="lp-card p-6 mt-6" id="project-empty" hidden>
                <p class="text-sm" style="color: var(--ink-soft);">
                    <span class="i18n-en">Nothing here uses that yet. Pick another filter to keep looking.</span><span class="i18n-id">Belum ada yang memakai itu. Pilih filter lain untuk melanjutkan.</span>
                </p>
            </div>
        @else
            <div class="lp-card p-6 mt-10" data-reveal>
                <p class="text-sm" style="color: var(--ink-soft);">
                    <span class="i18n-en">No projects have been published yet. Add one from the dashboard and it appears here.</span><span class="i18n-id">Belum ada proyek yang dipublikasikan. Tambahkan dari dasbor dan akan muncul di sini.</span>
                </p>
            </div>
        @endif
    </div>
</section>

<style>
    .project-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-block-start: clamp(2rem, 5vw, 3rem);
    }

    .project-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 20rem), 1fr));
        gap: 1rem;
        margin-block-start: 1.75rem;
    }

    .project-card { display: flex; flex-direction: column; overflow: hidden; }
    .project-card[hidden] { display: none; }

    .project-shot {
        position: relative;
        overflow: hidden;
        background-color: var(--surface);
        aspect-ratio: 16 / 10;
    }
    .project-shot img {
        inline-size: 100%;
        block-size: 100%;
        object-fit: cover;
        transition: transform 0.55s var(--ease-out), opacity 0.45s var(--ease-out);
    }
    /* Gradien tipis di kaki gambar menahan judul tetap terbaca di gambar terang. */
    .project-shot::after {
        content: '';
        position: absolute;
        inset-inline: 0;
        inset-block-end: 0;
        block-size: 35%;
        background-image: linear-gradient(to top, color-mix(in srgb, var(--surface) 70%, transparent), transparent);
        pointer-events: none;
    }
    @media (hover: hover) {
        .project-card:hover .project-shot img { transform: scale(1.04); }
    }

    .project-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 1.35rem;
        gap: 0.65rem;
        min-inline-size: 0;
    }
    .project-desc { font-size: 0.875rem; max-block-size: 3.4rem; }
    .project-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-block-start: auto;
        padding-block-start: 0.85rem;
    }

    .project-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem 1.25rem;
        margin-block-start: 0.5rem;
        padding-block-start: 0.85rem;
        border-block-start: 1px solid var(--rule);
    }
    .project-links a {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-block-size: 44px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--accent-text);
        transition: gap 0.24s var(--ease-out);
    }
    .project-links a i { font-size: 0.6875rem; }
    .project-links a:hover { gap: 0.7rem; text-decoration: underline; text-underline-offset: 3px; }

    /* Proyek terbaru diberi lebar ganda supaya daftar punya puncak, bukan grid rata. */
    @media (min-width: 1024px) {
        .project-card.is-wide { grid-column: span 2; flex-direction: row; }
        .project-card.is-wide .project-shot { inline-size: 52%; flex: none; aspect-ratio: auto; }
        .project-card.is-wide .project-shot::after { display: none; }
        .project-card.is-wide .project-body { padding: 1.9rem; }
        .project-card.is-wide .project-desc { max-block-size: 5.2rem; }
    }

    /* Kartu yang lolos filter masuk kembali bertahap, jadi pergantian daftar
       terbaca sebagai gerakan, bukan konten yang tiba-tiba berganti. */
    @keyframes project-in {
        from { opacity: 0; transform: translateY(14px) scale(0.98); }
        to { opacity: 1; transform: none; }
    }
    .project-card.is-enter { animation: project-in 0.45s var(--ease-out) both; animation-delay: var(--enter-delay, 0ms); }

    @media (prefers-reduced-motion: reduce) {
        .project-shot img, .project-links a { transition: none; }
        .project-card.is-enter { animation: none; }
    }
</style>

<script>
    (() => {
        const init = () => {
            const grid = document.getElementById('project-grid');
            if (!grid) return;

            const bar = document.getElementById('project-filters');
            const empty = document.getElementById('project-empty');
            const cards = grid.querySelectorAll('.project-card');

            // Dipanggil juga dari bagian Skills saat sebuah tech stack dipilih.
            window.lpFilterProjects = (filter) => {
                let shown = 0;

                cards.forEach((card) => {
                    const match = filter === 'all' || card.dataset.tech.split(' ').includes(filter);
                    card.hidden = !match;

                    if (!match) {
                        card.classList.remove('is-enter');
                        return;
                    }

                    card.style.setProperty('--enter-delay', (shown % 6) * 45 + 'ms');
                    // Melepas lalu memasang ulang kelasnya memaksa animasi diputar
                    // dari awal, bukan dilewati karena kelasnya sudah menempel.
                    card.classList.remove('is-enter');
                    void card.offsetWidth;
                    card.classList.add('is-enter');
                    shown++;
                });

                if (empty) empty.hidden = shown > 0;

                if (bar) {
                    bar.querySelectorAll('.lp-chip').forEach((chip) => {
                        chip.classList.toggle('is-on', chip.dataset.filter === filter);
                    });
                }
            };

            if (bar && !bar.dataset.bound) {
                bar.dataset.bound = '1';
                bar.addEventListener('click', (event) => {
                    const chip = event.target.closest('.lp-chip');
                    if (chip) window.lpFilterProjects(chip.dataset.filter);
                });
            }
        };

        document.addEventListener('livewire:navigated', init);
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, { once: true });
        } else {
            init();
        }
    })();
</script>
