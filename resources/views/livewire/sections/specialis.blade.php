<section id="specialis" class="lp-section">
    <div class="lp-shell">
        <p class="lp-slug" data-reveal>
            <span class="idx">04</span>
            <span class="i18n-en">Specialties</span><span class="i18n-id">Spesialisasi</span>
        </p>

        <h2 class="lp-h2 mt-6" data-reveal style="--reveal-delay:60ms">
            <span class="i18n-en">Where I do my best work</span><span class="i18n-id">Tempat saya bekerja paling baik</span>
        </h2>

        @if (count($specializations) > 0)
            <ul class="spec-list">
                @foreach ($specializations as $index => $specialization)
                    <li class="spec-row" data-reveal style="--reveal-delay:{{ min($index, 5) * 50 }}ms">
                        <span class="spec-idx">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="spec-icon" aria-hidden="true">
                            <i class="{{ $specialization->icon ?: 'fas fa-circle-nodes' }}"></i>
                        </span>
                        <h3 class="spec-name">{{ $specialization->name }}</h3>
                    </li>
                @endforeach
            </ul>

            @if (settings()->sectionEnabled('projects'))
                <a href="#projects" class="lp-btn lp-btn-ghost mt-12" data-reveal>
                    {!! bt('See these in practice') !!}
                    <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                </a>
            @endif
        @else
            <div class="lp-card p-6 mt-8" data-reveal>
                <p class="text-sm" style="color: var(--ink-soft);">
                    <span class="i18n-en">No specialties listed yet. Add them from the dashboard to fill this section.</span><span class="i18n-id">Belum ada spesialisasi yang terdaftar. Tambahkan dari dasbor untuk mengisi bagian ini.</span>
                </p>
            </div>
        @endif
    </div>
</section>

<style>
    .spec-list { margin-block-start: clamp(2.5rem, 6vw, 4rem); }

    .spec-row {
        position: relative;
        display: grid;
        grid-template-columns: auto auto 1fr;
        align-items: center;
        gap: 1rem;
        padding-block: clamp(1.25rem, 3vw, 2rem);
        padding-inline: clamp(0.5rem, 2vw, 1.25rem);
        border-block-start: 1px solid var(--rule);
        overflow: hidden;
    }
    .spec-row:last-child { border-block-end: 1px solid var(--rule); }

    /* Sapuan aksen mengisi baris dari kiri saat kursor mendekat; baris tetap
       terbaca sebagai daftar, bukan berubah jadi kartu. */
    .spec-row::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: linear-gradient(90deg, color-mix(in srgb, var(--primary) 9%, transparent), transparent 65%);
        transform: scaleX(0);
        transform-origin: left;
        opacity: 0;
        transition: transform 0.45s var(--ease-out), opacity 0.35s var(--ease-out);
        pointer-events: none;
    }
    @media (hover: hover) {
        .spec-row:hover::before { transform: none; opacity: 1; }
        .spec-row:hover .spec-icon { border-color: var(--primary); color: var(--on-primary); background-color: var(--primary); }
        .spec-row:hover .spec-name { transform: translateX(4px); }
    }

    .spec-idx {
        position: relative;
        font-family: var(--font-mono);
        font-size: 0.6875rem;
        color: var(--ink-soft);
        inline-size: 1.75rem;
    }
    .spec-icon {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 42px;
        block-size: 42px;
        flex: none;
        border-radius: 12px;
        border: 1px solid var(--hairline);
        background-color: var(--surface-alt);
        color: var(--accent-text);
        font-size: 1rem;
        transition: background-color 0.3s var(--ease-out),
                    border-color 0.3s var(--ease-out),
                    color 0.3s var(--ease-out);
    }
    .spec-name {
        position: relative;
        font-weight: 600;
        letter-spacing: -0.025em;
        line-height: 1.2;
        color: var(--ink);
        font-size: clamp(1.125rem, 3vw, 1.875rem);
        min-inline-size: 0;
        transition: transform 0.35s var(--ease-out);
    }

    @media (prefers-reduced-motion: reduce) {
        .spec-row::before { transition: none; }
        .spec-row:hover .spec-name { transform: none; }
    }
</style>
