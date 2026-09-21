@php
    $extras = collect(is_array($user->about_extra_information) ? $user->about_extra_information : [])
        ->pluck('information')
        ->filter();
@endphp

<section id="about" class="lp-section">
    <div class="lp-shell">
        <div class="grid lg:grid-cols-12 gap-x-14 gap-y-12">

            <div class="lg:col-span-5">
                <div class="lg:sticky" style="top: calc(var(--nav-h) + 2rem);" data-reveal>
                    <div class="about-shot sk-frame">
                        <img src="{{ safe_image_url($user->about_image) }}"
                             alt="{{ $user->name ?? 'About' }}"
                             loading="lazy"
                             class="sk-img">
                    </div>

                    @if ($user->experience)
                        <p class="about-years">
                            <span class="about-years-num">{{ $user->experience }}</span>
                            <span class="i18n-en">years of work behind this page</span><span class="i18n-id">tahun kerja di balik halaman ini</span>
                        </p>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-7">
                <p class="lp-slug" data-reveal>
                    <span class="idx">01</span>
                    <span class="i18n-en">About</span><span class="i18n-id">Tentang</span>
                </p>

                @if ($user->about_title)
                    <h2 class="lp-h2 mt-6" data-reveal style="--reveal-delay:60ms">{!! bt_dynamic($user->about_title) !!}</h2>
                @endif

                @if ($user->about_description)
                    <div class="prose-content mt-6" data-reveal style="--reveal-delay:100ms; font-size: 1.0625rem;">
                        {!! bt_dynamic($user->about_description, html: true) !!}
                    </div>
                @endif

                @if ($extras->isNotEmpty())
                    <ul class="about-points" data-reveal style="--reveal-delay:140ms">
                        @foreach ($extras as $item)
                            <li>
                                <i class="fas fa-check" aria-hidden="true"></i>
                                <span>{!! bt_dynamic($item) !!}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
    .about-shot {
        border-radius: var(--r-slab);
        border: 1px solid var(--hairline);
        background-color: var(--surface-alt);
        padding: 6px;
        box-shadow: var(--shadow-md), var(--inner-lift);
        aspect-ratio: 5 / 4;
    }
    .about-shot img {
        inline-size: 100%;
        block-size: 100%;
        object-fit: cover;
        border-radius: calc(var(--r-slab) - 6px);
    }

    .about-years {
        display: flex;
        align-items: baseline;
        gap: 0.55rem;
        margin-block-start: 1.25rem;
        font-family: var(--font-mono);
        font-size: 0.75rem;
        color: var(--ink-soft);
    }
    .about-years-num {
        font-family: inherit;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: -0.03em;
        color: var(--accent-text);
    }

    .about-points {
        display: grid;
        gap: 0.5rem;
        margin-block-start: 2.5rem;
    }
    .about-points li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border: 1px solid var(--hairline);
        border-radius: var(--r-ctl);
        background-color: var(--surface-alt);
        box-shadow: var(--inner-lift);
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--ink);
        transition: border-color 0.24s var(--ease-out), transform 0.24s var(--ease-out);
    }
    @media (hover: hover) {
        .about-points li:hover {
            border-color: color-mix(in srgb, var(--primary) 45%, var(--hairline));
            transform: translateX(3px);
        }
    }
    .about-points i {
        margin-block-start: 0.3rem;
        flex: none;
        font-size: 0.6875rem;
        color: var(--accent-text);
    }

    @media (min-width: 640px) {
        .about-points { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (prefers-reduced-motion: reduce) {
        .about-points li:hover { transform: none; }
    }
</style>
