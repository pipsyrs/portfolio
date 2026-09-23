<section id="certifications" class="lp-section">
    <div class="lp-shell">
        <div class="flex flex-wrap items-end justify-between gap-5">
            <div>
                <p class="lp-slug" data-reveal>
                    <span class="idx">06</span>
                    <span class="i18n-en">Certifications</span><span class="i18n-id">Sertifikasi</span>
                </p>
                <h2 class="lp-h2 mt-6" data-reveal style="--reveal-delay:60ms">
                    <span class="i18n-en">Papers on the wall</span><span class="i18n-id">Sertifikat yang saya pegang</span>
                </h2>
            </div>
            @if (count($certifications) > 0)
                <p class="lp-meta" data-reveal>{{ count($certifications) }}
                    <span class="i18n-en">on record</span><span class="i18n-id">tercatat</span>
                </p>
            @endif
        </div>

        @if (count($certifications) > 0)
            <div class="cert-grid">
                @foreach ($certifications as $index => $cert)
                    @php
                        $issuedEn = ! empty($cert['issued_at']) ? \Carbon\Carbon::parse($cert['issued_at'])->locale('en')->translatedFormat('M Y') : '';
                        $issuedId = ! empty($cert['issued_at']) ? \Carbon\Carbon::parse($cert['issued_at'])->locale('id')->translatedFormat('M Y') : '';

                        $noExpiry = ! empty($cert['no_expiry']) || empty($cert['expired_at']);
                        $expired = ! $noExpiry && \Carbon\Carbon::parse($cert['expired_at'])->isPast();
                        $expiredEn = ! $noExpiry ? \Carbon\Carbon::parse($cert['expired_at'])->locale('en')->translatedFormat('M Y') : '';
                        $expiredId = ! $noExpiry ? \Carbon\Carbon::parse($cert['expired_at'])->locale('id')->translatedFormat('M Y') : '';

                        $ext = ! empty($cert['file']) ? strtolower(pathinfo($cert['file'], PATHINFO_EXTENSION)) : null;
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                    @endphp

                    <article class="cert-card lp-card lp-card-int" data-reveal style="--reveal-delay:{{ min($index, 5) * 45 }}ms">
                        <div class="cert-top">
                            @if (! empty($cert['file']) && $isImage)
                                <span class="cert-thumb sk-frame">
                                    <img src="{{ safe_image_url($cert['file']) }}" alt="" loading="lazy" class="sk-img">
                                </span>
                            @else
                                <span class="cert-thumb cert-thumb--blank" aria-hidden="true">
                                    <i class="{{ $ext === 'pdf' ? 'fas fa-file-pdf' : 'fas fa-certificate' }}"></i>
                                </span>
                            @endif

                            <div class="cert-main">
                                <h3 class="cert-title">{{ $cert['title'] ?? '' }}</h3>
                                @if (! empty($cert['issuer']))
                                    <p class="cert-issuer">{{ $cert['issuer'] }}</p>
                                @endif
                            </div>

                            @if ($expired)
                                <span class="cert-flag is-off">{!! bt('Expired') !!}</span>
                            @elseif ($noExpiry)
                                <span class="cert-flag is-on">{!! bt('No Expiry') !!}</span>
                            @else
                                <span class="cert-flag is-on">{!! bt('Valid') !!}</span>
                            @endif
                        </div>

                        <div class="flex justify-between">
                            <dl class="cert-period lp-meta">
                                @if ($issuedEn)
                                    <dt class="sr-only"><span class="i18n-en">Issued</span><span class="i18n-id">Terbit</span></dt>
                                    <dd><span class="i18n-en">{{ $issuedEn }}</span><span class="i18n-id">{{ $issuedId }}</span></dd>
                                @endif
                                @if (! $noExpiry)
                                    <span class="cert-sep" aria-hidden="true"></span>
                                    <dt class="sr-only"><span class="i18n-en">Expires</span><span class="i18n-id">Berakhir</span></dt>
                                    <dd><span class="i18n-en">{{ $expiredEn }}</span><span class="i18n-id">{{ $expiredId }}</span></dd>
                                @endif
                            </dl>

                            <dl class="lp-meta">
                                @if (! empty($cert['credential_id']))
                                    <dd class="cert-credential">{{ $cert['credential_id'] }}</dd>
                                @endif
                            </dl>
                        </div>

                        @if (! empty($cert['file']) || ! empty($cert['credential_url']))
                            <div class="cert-links">
                                @if (! empty($cert['file']))
                                    <a href="{{ safe_image_url($cert['file']) }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                        {!! bt('View Certificate') !!}
                                    </a>
                                @endif
                                @if (! empty($cert['credential_url']))
                                    <a href="{{ $cert['credential_url'] }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-shield-halved" aria-hidden="true"></i>
                                        {!! bt('View Credential') !!}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="lp-card p-6 mt-10" data-reveal>
                <p class="text-sm" style="color: var(--ink-soft);">
                    <span class="i18n-en">No certificates have been added yet. Upload them from the dashboard to list them here.</span><span class="i18n-id">Belum ada sertifikat yang ditambahkan. Unggah dari dasbor untuk menampilkannya di sini.</span>
                </p>
            </div>
        @endif
    </div>
</section>

<style>
    .cert-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 24rem), 1fr));
        gap: 0.9rem;
        margin-block-start: clamp(2.5rem, 6vw, 4rem);
    }

    .cert-card {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
        padding: clamp(1.1rem, 3vw, 1.5rem);
    }

    .cert-top {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: start;
        gap: 0.9rem;
    }

    .cert-thumb {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 52px;
        block-size: 52px;
        flex: none;
        border-radius: 13px;
        border: 1px solid var(--hairline);
        background-color: var(--surface);
        overflow: hidden;
    }
    .cert-thumb img { inline-size: 100%; block-size: 100%; object-fit: cover; }
    .cert-thumb--blank { color: var(--accent-text); font-size: 1.15rem; }

    .cert-main { min-inline-size: 0; }
    .cert-title {
        font-weight: 600;
        line-height: 1.35;
        letter-spacing: -0.015em;
        color: var(--ink);
        font-size: 1.0625rem;
    }
    .cert-issuer { margin-block-start: 0.2rem; font-size: 0.8125rem; font-weight: 500; color: var(--accent-text); }

    .cert-flag {
        display: inline-flex;
        align-items: center;
        flex: none;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        border: 1px solid var(--hairline);
        font-family: var(--font-mono);
        font-size: 0.625rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--ink-soft);
    }
    .cert-flag.is-on {
        color: color-mix(in srgb, var(--success) 45%, var(--ink));
        border-color: color-mix(in srgb, var(--success) 40%, var(--hairline));
        background-color: color-mix(in srgb, var(--success) 10%, transparent);
    }
    .cert-flag.is-off {
        color: color-mix(in srgb, var(--danger) 55%, var(--ink));
        border-color: color-mix(in srgb, var(--danger) 40%, var(--hairline));
        background-color: color-mix(in srgb, var(--danger) 10%, transparent);
    }

    .cert-period {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem 0.6rem;
    }
    .cert-sep {
        inline-size: 0.7rem;
        block-size: 1px;
        flex: none;
        background-color: var(--ink-soft);
        opacity: 0.5;
    }
    .cert-credential { overflow-wrap: anywhere; }

    .cert-links {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.25rem 1.25rem;
        margin-block-start: auto;
        padding-block-start: 0.85rem;
        border-block-start: 1px solid var(--rule);
    }
    .cert-links a {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-block-size: 44px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--accent-text);
        transition: gap 0.24s var(--ease-out);
    }
    .cert-links a i { font-size: 0.6875rem; }
    .cert-links a:hover { gap: 0.7rem; text-decoration: underline; text-underline-offset: 3px; }

    @media (prefers-reduced-motion: reduce) {
        .cert-links a, .cert-links a:hover { transition: none; gap: 0.45rem; }
    }
</style>
