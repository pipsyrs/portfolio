@php
    $socials = collect([
        ['link' => settings('github_link'), 'icon' => 'fab fa-github', 'label' => 'GitHub'],
        ['link' => settings('linkedin_link'), 'icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn'],
        ['link' => settings('x_twitter_link'), 'icon' => 'fab fa-x-twitter', 'label' => 'X'],
        ['link' => settings('instagram_link'), 'icon' => 'fab fa-instagram', 'label' => 'Instagram'],
        ['link' => settings('youtube_link'), 'icon' => 'fab fa-youtube', 'label' => 'YouTube'],
        ['link' => settings('tiktok_link'), 'icon' => 'fab fa-tiktok', 'label' => 'TikTok'],
        ['link' => settings('facebook_link'), 'icon' => 'fab fa-facebook-f', 'label' => 'Facebook'],
    ])->filter(fn (array $social) => filled($social['link']));
@endphp

<footer class="lp-section lp-section--tight" style="border-block-start: 1px solid var(--hairline);">
    <div class="lp-shell">
        <div class="footer-top-row">
            <div>
                <p class="footer-name">{{ $user->name ?? 'Portfolio' }}<i>.</i></p>
                @if ($user->specialis)
                    <p class="lp-meta mt-3">{{ $user->specialis }}</p>
                @endif
            </div>

            @if ($socials->isNotEmpty())
                <div>
                    <p class="lp-meta mb-3">{!! bt('Connect') !!}</p>
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($socials as $social)
                            <li>
                                <a href="{{ $social['link'] }}" target="_blank" rel="noopener noreferrer"
                                   class="lp-icon-btn footer-social" aria-label="{{ $social['label'] }}">
                                    <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="footer-base">
            <p>&copy; {{ date('Y') }} {{ $user->name }}. {!! bt('All Rights Reserved.') !!}</p>
            <a href="#hero" class="footer-top">
                <i class="fas fa-arrow-up" aria-hidden="true"></i>
                {!! bt('Back to top') !!}
            </a>
        </div>
    </div>
</footer>

<style>
    .footer-top-row {
        display: flex;
        flex-direction: column;
        gap: 2.5rem;
    }

    /* Nama ditutup sebesar pembukanya, jadi halaman terasa punya bingkai. */
    .footer-name {
        font-weight: 800;
        letter-spacing: -0.045em;
        line-height: 0.95;
        color: var(--ink);
        font-size: clamp(2.25rem, 7vw, 4.5rem);
        overflow-wrap: break-word;
    }
    .footer-name i { font-style: normal; color: var(--accent-text); }

    .footer-social { transition: background-color 0.24s var(--ease-out), color 0.24s var(--ease-out), border-color 0.24s var(--ease-out), transform 0.24s var(--ease-out); }
    .footer-social:hover {
        background-color: var(--primary);
        border-color: var(--primary);
        color: var(--on-primary);
        transform: translateY(-3px);
    }

    .footer-base {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem 1.5rem;
        margin-block-start: clamp(2.5rem, 6vw, 4rem);
        padding-block-start: 1.5rem;
        border-block-start: 1px solid var(--rule);
        font-size: 0.8125rem;
        color: var(--ink-soft);
    }
    .footer-top {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-block-size: 44px;
        font-weight: 600;
        color: var(--accent-text);
        transition: gap 0.24s var(--ease-out);
    }
    .footer-top i { font-size: 0.6875rem; }
    .footer-top:hover { gap: 0.75rem; text-decoration: underline; text-underline-offset: 3px; }

    @media (min-width: 768px) {
        .footer-top-row { flex-direction: row; align-items: flex-start; justify-content: space-between; }
    }

    @media (prefers-reduced-motion: reduce) {
        .footer-social:hover { transform: none; }
        .footer-top, .footer-top:hover { transition: none; gap: 0.5rem; }
    }
</style>
