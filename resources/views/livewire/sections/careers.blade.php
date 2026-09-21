@php
    $careers = is_array($user->careers) ? $user->careers : [];

    $month = fn (?string $date, string $locale) => $date
        ? \Carbon\Carbon::parse($date)->locale($locale)->translatedFormat('M Y')
        : '';
@endphp

<section id="careers" class="lp-section lp-section--open">
    <div class="lp-shell">
        <p class="lp-slug" data-reveal>
            <span class="idx">05</span>
            <span class="i18n-en">Careers</span><span class="i18n-id">Karier</span>
        </p>

        <h2 class="lp-h2 mt-6" data-reveal style="--reveal-delay:60ms">
            <span class="i18n-en">Where I have worked</span><span class="i18n-id">Tempat saya pernah bekerja</span>
        </h2>

        @if (count($careers) > 0)
            <p class="lp-lead mt-5" data-reveal style="--reveal-delay:100ms">
                <span class="i18n-en">Open a role to read what the work involved.</span><span class="i18n-id">Buka satu peran untuk membaca isi pekerjaannya.</span>
            </p>

            <ol class="career-rail" id="career-rail">
                @foreach ($careers as $index => $career)
                    @php
                        $ongoing = ! empty($career['on_going']);
                        $startEn = $month($career['start_date'] ?? null, 'en');
                        $startId = $month($career['start_date'] ?? null, 'id');
                        $endEn = $month($career['end_date'] ?? null, 'en');
                        $endId = $month($career['end_date'] ?? null, 'id');
                        $hasBody = ! empty($career['description']);
                        $open = $index === 0;
                        $bodyId = 'career-body-'.$index;
                    @endphp

                    <li class="career-item {{ $hasBody && $open ? 'is-open' : '' }}"
                        data-reveal style="--reveal-delay:{{ min($index, 5) * 50 }}ms">
                        <span class="career-dot {{ $ongoing ? 'is-live' : '' }}" aria-hidden="true"></span>

                        @if ($hasBody)
                            <button type="button" class="career-head" aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $bodyId }}">
                        @else
                            <div class="career-head">
                        @endif

                            @if (! empty($career['logo']))
                                <span class="career-logo sk-frame">
                                    <img src="{{ safe_image_url($career['logo'], 'careers-logo') }}"
                                         alt="{{ $career['company'] ?? '' }}"
                                         loading="lazy"
                                         class="sk-img">
                                </span>
                            @else
                                <span class="career-logo career-logo--blank" aria-hidden="true">
                                    <i class="fas fa-building"></i>
                                </span>
                            @endif

                            <span class="career-titles">
                                <span class="career-role">{{ $career['position'] ?? '' }}</span>
                                <span class="career-company">{{ $career['company'] ?? '' }}</span>
                            </span>

                            <span class="career-when">
                                <span class="lp-meta">
                                    @if ($ongoing)
                                        <span class="i18n-en">Since {{ $startEn }}</span><span class="i18n-id">Sejak {{ $startId }}</span>
                                    @else
                                        <span class="i18n-en">{{ $startEn }} to {{ $endEn }}</span><span class="i18n-id">{{ $startId }} sampai {{ $endId }}</span>
                                    @endif
                                </span>
                                @if ($ongoing)
                                    <span class="career-live">
                                        <span class="career-live-dot" aria-hidden="true"></span>
                                        {!! bt('Active') !!}
                                    </span>
                                @endif
                            </span>

                            @if ($hasBody)
                                <span class="career-caret" aria-hidden="true">
                                    <i class="fas fa-chevron-down"></i>
                                </span>
                            @endif

                        @if ($hasBody)
                            </button>
                        @else
                            </div>
                        @endif

                        @if ($hasBody)
                            {{-- grid-template-rows 0fr -> 1fr membuat tinggi terbuka ikut
                                 dianimasikan tanpa perlu mengukur isinya lewat JS. --}}
                            <div class="career-body-wrap" id="{{ $bodyId }}">
                                <div class="career-body-clip">
                                    <div class="career-body prose-content">
                                        {!! bt_dynamic($career['description'], html: true) !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ol>
        @else
            <div class="lp-card p-6 mt-8" data-reveal>
                <p class="text-sm" style="color: var(--ink-soft);">
                    <span class="i18n-en">No roles have been added yet. Add them from the dashboard to build this timeline.</span><span class="i18n-id">Belum ada peran yang ditambahkan. Tambahkan dari dasbor untuk menyusun lini masa ini.</span>
                </p>
            </div>
        @endif
    </div>
</section>

<style>
    .career-rail {
        position: relative;
        margin-block-start: clamp(2.5rem, 6vw, 4rem);
        padding-inline-start: 1.75rem;
    }
    .career-rail::before {
        content: '';
        position: absolute;
        inset-block: 1rem;
        inset-inline-start: 3px;
        inline-size: 1px;
        background-image: linear-gradient(180deg, var(--primary), var(--rule) 25%, var(--rule) 75%, transparent);
    }

    .career-item { position: relative; border-block-start: 1px solid var(--rule); }
    .career-item:first-child { border-block-start: 0; }

    .career-dot {
        position: absolute;
        inset-inline-start: -1.75rem;
        inset-block-start: 1.7rem;
        inline-size: 9px;
        block-size: 9px;
        margin-inline-start: -2px;
        border-radius: 999px;
        border: 2px solid var(--surface);
        box-sizing: content-box;
        background-color: var(--ink-soft);
    }
    .career-dot.is-live {
        background-color: var(--primary);
        box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary) 18%, transparent);
    }

    .career-head {
        inline-size: 100%;
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 0.5rem 1rem;
        padding-block: 1.25rem;
        padding-inline: 0.25rem;
        text-align: start;
        border-radius: var(--r-ctl);
        transition: background-color 0.24s var(--ease-out);
    }
    button.career-head { cursor: pointer; }
    @media (hover: hover) {
        button.career-head:hover { background-color: color-mix(in srgb, var(--ink) 3%, transparent); }
        button.career-head:hover .career-role { color: var(--accent-text); }
    }

    .career-logo {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 46px;
        block-size: 46px;
        flex: none;
        border-radius: 12px;
        border: 1px solid var(--hairline);
        background-color: var(--surface-alt);
        overflow: hidden;
    }
    .career-logo img {
        inline-size: 100%;
        block-size: 100%;
        object-fit: contain;
        padding: 6px;
    }
    .career-logo--blank { color: var(--ink-soft); font-size: 0.9375rem; }

    .career-titles { display: flex; flex-direction: column; gap: 0.2rem; min-inline-size: 0; }
    .career-role, .career-company { overflow-wrap: break-word; }
    .career-role {
        font-weight: 600;
        letter-spacing: -0.02em;
        color: var(--ink);
        font-size: clamp(1.0625rem, 2.2vw, 1.3125rem);
        line-height: 1.3;
        transition: color 0.24s var(--ease-out);
    }
    .career-company { font-size: 0.875rem; font-weight: 500; color: var(--accent-text); }

    .career-when {
        grid-column: 2 / -1;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 0.85rem;
    }
    .career-live {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background-color: color-mix(in srgb, var(--primary) 12%, transparent);
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--accent-text);
    }
    .career-live-dot {
        inline-size: 6px;
        block-size: 6px;
        border-radius: 999px;
        background-color: var(--primary);
    }

    .career-caret {
        grid-row: 1;
        grid-column: 3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 34px;
        block-size: 34px;
        border-radius: 999px;
        border: 1px solid var(--hairline);
        font-size: 0.6875rem;
        color: var(--ink-soft);
        transition: transform 0.35s var(--ease-out),
                    border-color 0.24s var(--ease-out),
                    color 0.24s var(--ease-out);
    }
    .career-item.is-open .career-caret {
        transform: rotate(180deg);
        border-color: var(--primary);
        color: var(--accent-text);
    }

    .career-body-wrap {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows 0.42s var(--ease-out);
    }
    .career-item.is-open .career-body-wrap { grid-template-rows: 1fr; }
    .career-body-clip { overflow: hidden; }
    .career-body {
        padding-block: 0 1.5rem;
        padding-inline: 0.25rem;
        font-size: 0.9375rem;
        max-inline-size: 62ch;
        opacity: 0;
        transition: opacity 0.35s var(--ease-out) 0.08s;
    }
    .career-item.is-open .career-body { opacity: 1; }

    @media (min-width: 640px) {
        .career-rail { padding-inline-start: 2.25rem; }
        .career-dot { inset-inline-start: -2.25rem; }
        .career-head { grid-template-columns: auto 1fr auto auto; }
        .career-when { grid-column: 3; grid-row: 1; justify-content: flex-end; }
        .career-caret { grid-column: 4; }
    }

    @media (prefers-reduced-motion: reduce) {
        .career-caret, .career-body-wrap, .career-body { transition: none; }
    }
</style>

<script>
    (() => {
        const init = () => {
            const rail = document.getElementById('career-rail');
            if (!rail || rail.dataset.bound) return;
            rail.dataset.bound = '1';

            rail.addEventListener('click', (event) => {
                const head = event.target.closest('button.career-head');
                if (!head) return;

                const item = head.closest('.career-item');
                const open = head.getAttribute('aria-expanded') === 'true';

                head.setAttribute('aria-expanded', open ? 'false' : 'true');
                item?.classList.toggle('is-open', !open);
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
