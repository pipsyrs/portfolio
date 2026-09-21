@php
    $canFilterProjects = settings()->sectionEnabled('projects');
@endphp

<section id="tech" class="lp-section">
    <div class="lp-shell">
        <div class="grid lg:grid-cols-12 gap-x-14 gap-y-10">

            <div class="lg:col-span-4">
                <div class="lg:sticky" style="top: calc(var(--nav-h) + 2rem);">
                    <p class="lp-slug" data-reveal>
                        <span class="idx">02</span>
                        <span class="i18n-en">Skills</span><span class="i18n-id">Keahlian</span>
                    </p>
                    <h2 class="lp-h2 mt-6" data-reveal style="--reveal-delay:60ms">
                        <span class="i18n-en">What I reach for</span><span class="i18n-id">Yang saya pakai</span>
                    </h2>
                    @if ($canFilterProjects && count($techStacks) > 0)
                        <p class="lp-lead mt-5" data-reveal style="--reveal-delay:100ms">
                            <span class="i18n-en">Pick one to see the projects built with it.</span><span class="i18n-id">Pilih satu untuk melihat proyek yang memakainya.</span>
                        </p>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-8">
                @if (count($techStacks) > 0)
                    <div class="tech-grid" id="tech-list">
                        @foreach ($techStacks as $index => $stack)
                            @if ($canFilterProjects)
                                <button type="button" class="tech-tile" data-tech-jump="{{ Str::slug($stack->name) }}"
                                        data-reveal style="--reveal-delay:{{ min($index, 9) * 35 }}ms">
                                    <span class="tech-icon" aria-hidden="true">
                                        <i class="{{ $stack->icon ?: 'fas fa-code' }}"></i>
                                    </span>
                                    <span class="tech-name">{{ $stack->name }}</span>
                                    <i class="fas fa-arrow-right tech-go" aria-hidden="true"></i>
                                </button>
                            @else
                                <div class="tech-tile" data-reveal style="--reveal-delay:{{ min($index, 9) * 35 }}ms">
                                    <span class="tech-icon" aria-hidden="true">
                                        <i class="{{ $stack->icon ?: 'fas fa-code' }}"></i>
                                    </span>
                                    <span class="tech-name">{{ $stack->name }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="lp-card p-6" data-reveal>
                        <p class="text-sm" style="color: var(--ink-soft);">
                            <span class="i18n-en">No tech stack has been added yet. Add one from the dashboard and it shows up here.</span><span class="i18n-id">Belum ada tech stack yang ditambahkan. Tambahkan dari dasbor dan akan muncul di sini.</span>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
    .tech-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 11.5rem), 1fr));
        gap: 0.65rem;
    }

    .tech-tile {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-block-size: 64px;
        padding: 0.75rem 0.9rem;
        border-radius: var(--r-card);
        background-color: var(--glass-bg);
        backdrop-filter: blur(var(--glass-blur)) saturate(165%);
        -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(165%);
        border: 1px solid var(--glass-brd);
        box-shadow: inset 0 1px 0 var(--glass-sheen);
        text-align: start;
        min-inline-size: 0;
        transition: border-color 0.26s var(--ease-out),
                    transform 0.26s var(--ease-out),
                    box-shadow 0.26s var(--ease-out);
    }
    button.tech-tile { cursor: pointer; }

    .tech-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 38px;
        block-size: 38px;
        flex: none;
        border-radius: 11px;
        border: 1px solid var(--hairline);
        background-color: var(--surface);
        color: var(--accent-text);
        font-size: 1rem;
        transition: background-color 0.26s var(--ease-out),
                    color 0.26s var(--ease-out),
                    border-color 0.26s var(--ease-out);
    }

    .tech-name {
        flex: 1;
        min-inline-size: 0;
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--ink);
        overflow-wrap: anywhere;
    }

    /* Panah baru muncul saat kursor mendekat: petunjuk bahwa ubin ini bisa diklik. */
    .tech-go {
        flex: none;
        font-size: 0.6875rem;
        color: var(--ink-soft);
        opacity: 0;
        transform: translateX(-4px);
        transition: opacity 0.24s var(--ease-out), transform 0.24s var(--ease-out);
    }

    @media (hover: hover) {
        button.tech-tile:hover {
            border-color: color-mix(in srgb, var(--primary) 50%, var(--hairline));
            transform: translateY(-3px);
            box-shadow: var(--shadow-md), var(--inner-lift);
        }
        button.tech-tile:hover .tech-icon {
            background-color: var(--primary);
            border-color: var(--primary);
            color: var(--on-primary);
        }
        button.tech-tile:hover .tech-go { opacity: 1; transform: none; }
    }
    button.tech-tile:active { transform: translateY(0) scale(0.985); }

    @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
        .tech-tile { background-color: var(--surface-alt); border-color: var(--hairline); }
    }

    @media (prefers-reduced-motion: reduce) {
        button.tech-tile:hover { transform: none; }
        .tech-go { opacity: 1; transform: none; }
    }
</style>

@if ($canFilterProjects && count($techStacks) > 0)
    <script>
        (() => {
            const init = () => {
                const list = document.getElementById('tech-list');
                if (!list || list.dataset.bound) return;
                list.dataset.bound = '1';

                list.addEventListener('click', (event) => {
                    const tile = event.target.closest('[data-tech-jump]');
                    if (!tile) return;

                    if (typeof window.lpFilterProjects === 'function') {
                        window.lpFilterProjects(tile.dataset.techJump);
                    }

                    document.getElementById('projects')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            };

            document.addEventListener('livewire:navigated', init);
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init, { once: true });
            } else {
                init();
            }
        })();
    </script>
@endif
