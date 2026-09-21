@php
    /**
     * Poin di bawah ini mencerminkan mekanisme yang benar-benar dipakai aplikasi
     * ini — 2FA, session absolut, signed URL, rate limiter, disk privat, dan
     * header respons — bukan daftar klaim umum. Perbarui bila mekanismenya berubah.
     */
    $measures = [
        [
            'icon' => 'fa-solid fa-fingerprint',
            'en' => 'Two-factor authentication',
            'id' => 'Autentikasi dua faktor',
            'body_en' => 'Owner sign-in is backed by TOTP with recovery codes, so a leaked password alone opens nothing.',
            'body_id' => 'Masuk pemilik dilindungi TOTP beserta kode pemulihan, jadi kata sandi yang bocor saja tidak membuka apa pun.',
        ],
        [
            'icon' => 'fa-solid fa-hourglass-half',
            'en' => 'Absolute session lifetime',
            'id' => 'Umur sesi absolut',
            'body_en' => 'Sessions expire on a hard clock and stay bound to their origin, not just on idle timeout.',
            'body_id' => 'Sesi berakhir pada batas waktu tegas dan tetap terikat asalnya, bukan sekadar mati saat menganggur.',
        ],
        [
            'icon' => 'fa-solid fa-signature',
            'en' => 'Signed, expiring links',
            'id' => 'Tautan bertanda tangan',
            'body_en' => 'Protected downloads run through short-lived signed URLs, so a leaked link cannot be replayed later.',
            'body_id' => 'Unduhan terlindungi lewat URL bertanda tangan berumur pendek, jadi tautan yang bocor tidak bisa dipakai ulang.',
        ],
        [
            'icon' => 'fa-solid fa-gauge-high',
            'en' => 'Rate limited entry points',
            'id' => 'Titik masuk dibatasi laju',
            'body_en' => 'Public forms are throttled per address, which keeps brute force and mail flooding off the table.',
            'body_id' => 'Formulir publik dibatasi per alamat, sehingga brute force dan banjir email tidak jadi pilihan.',
        ],
        [
            'icon' => 'fa-solid fa-file-shield',
            'en' => 'Private storage, verified files',
            'id' => 'Penyimpanan privat, berkas terverifikasi',
            'body_en' => 'Sensitive uploads live outside the public root and are checked by content, not by the extension they claim.',
            'body_id' => 'Unggahan sensitif disimpan di luar akar publik dan diperiksa dari isinya, bukan dari ekstensi yang diklaim.',
        ],
        [
            'icon' => 'fa-solid fa-lock',
            'en' => 'Hardened responses',
            'id' => 'Respons yang diperketat',
            'body_en' => 'Served documents carry a strict content policy and nosniff, so the browser cannot be talked into running them.',
            'body_id' => 'Dokumen yang disajikan membawa kebijakan konten ketat dan nosniff, jadi peramban tidak bisa dibujuk menjalankannya.',
        ],
    ];
@endphp

<section id="security" class="lp-section">
    <div class="lp-shell">
        <div class="sec-head">
            <div>
                <p class="lp-slug" data-reveal>
                    <span class="idx">03</span>
                    <span class="i18n-en">Security</span><span class="i18n-id">Keamanan</span>
                </p>

                <h2 class="lp-h2 mt-6" data-reveal style="--reveal-delay:60ms">
                    <span class="i18n-en">Built to be</span><span class="i18n-id">Dibangun untuk</span>
                    <span class="lp-grad"><span class="i18n-en">hard to break</span><span class="i18n-id">sulit ditembus</span></span>
                </h2>

                <p class="lp-lead mt-5" data-reveal style="--reveal-delay:100ms">
                    <span class="i18n-en">Every measure below is wired into this very site, not a checklist I keep on a slide.</span><span class="i18n-id">Setiap langkah di bawah ini terpasang di situs ini sendiri, bukan daftar periksa yang cuma ada di slide.</span>
                </p>
            </div>

            <p class="sec-seal lp-glass" data-reveal style="--reveal-delay:140ms">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                <span class="sec-seal-text">
                    <span class="sec-seal-count">{{ count($measures) }}</span>
                    <span class="i18n-en">controls in place</span><span class="i18n-id">kendali terpasang</span>
                </span>
            </p>
        </div>

        <ul class="sec-grid">
            @foreach ($measures as $index => $measure)
                <li class="sec-card lp-glass lp-card-int" data-reveal style="--reveal-delay:{{ min($index, 5) * 45 }}ms">
                    <span class="sec-icon" aria-hidden="true">
                        <i class="{{ $measure['icon'] }}"></i>
                    </span>

                    <h3 class="sec-title">
                        <span class="i18n-en">{{ $measure['en'] }}</span><span class="i18n-id">{{ $measure['id'] }}</span>
                    </h3>

                    <p class="sec-body">
                        <span class="i18n-en">{{ $measure['body_en'] }}</span><span class="i18n-id">{{ $measure['body_id'] }}</span>
                    </p>

                    <span class="sec-flag">
                        <span class="sec-flag-dot" aria-hidden="true"></span>
                        <span class="i18n-en">Enforced</span><span class="i18n-id">Diterapkan</span>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</section>

<style>
    .sec-head {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    /* Segel jumlah kendali: satu-satunya angka di bagian ini, jadi ia yang
       menahan pandangan sebelum daftar kartunya dibaca. */
    .sec-seal {
        display: inline-flex;
        align-items: center;
        gap: 0.85rem;
        align-self: flex-start;
        padding: 0.9rem 1.25rem;
        border-radius: 999px;
    }
    .sec-seal > i { font-size: 1.35rem; color: var(--accent-text); }
    .sec-seal-text {
        display: flex;
        flex-direction: column;
        font-family: var(--font-mono);
        font-size: 0.6875rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--ink-soft);
    }
    .sec-seal-count {
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: -0.03em;
        line-height: 1;
        color: var(--ink);
    }

    .sec-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 19rem), 1fr));
        gap: 0.9rem;
        margin-block-start: clamp(2.5rem, 6vw, 4rem);
    }

    .sec-card {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
        padding: clamp(1.25rem, 3vw, 1.6rem);
        border-radius: var(--r-card);
        overflow: hidden;
    }

    /* Raster halus di sudut kartu: isyarat teknis, cukup samar untuk tidak
       mengganggu teks yang menumpang di atasnya. */
    .sec-card::after {
        content: '';
        position: absolute;
        inset-block-start: 0;
        inset-inline-end: 0;
        inline-size: 9rem;
        block-size: 9rem;
        background-image: radial-gradient(color-mix(in srgb, var(--ink) 22%, transparent) 1px, transparent 1px);
        background-size: 9px 9px;
        -webkit-mask-image: radial-gradient(closest-side at 100% 0%, black, transparent);
        mask-image: radial-gradient(closest-side at 100% 0%, black, transparent);
        opacity: 0.35;
        pointer-events: none;
    }

    .sec-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 44px;
        block-size: 44px;
        border-radius: 13px;
        border: 1px solid var(--glass-brd);
        background-color: color-mix(in srgb, var(--primary) 14%, transparent);
        color: var(--accent-text);
        font-size: 1.0625rem;
        transition: background-color 0.3s var(--ease-out), color 0.3s var(--ease-out);
    }
    @media (hover: hover) {
        .sec-card:hover .sec-icon { background-color: var(--primary); color: var(--on-primary); }
    }

    .sec-title {
        font-size: 1.0625rem;
        font-weight: 600;
        letter-spacing: -0.015em;
        line-height: 1.3;
        color: var(--ink);
    }
    .sec-body { font-size: 0.9375rem; line-height: 1.6; color: var(--ink-soft); }

    .sec-flag {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        align-self: flex-start;
        margin-block-start: auto;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        border: 1px solid color-mix(in srgb, var(--success) 35%, transparent);
        background-color: color-mix(in srgb, var(--success) 10%, transparent);
        font-family: var(--font-mono);
        font-size: 0.625rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: color-mix(in srgb, var(--success) 45%, var(--ink));
    }
    .sec-flag-dot {
        inline-size: 6px;
        block-size: 6px;
        flex: none;
        border-radius: 999px;
        background-color: var(--success);
    }

    @media (min-width: 900px) {
        .sec-head { flex-direction: row; align-items: flex-end; justify-content: space-between; gap: 3rem; }
        .sec-seal { align-self: auto; }
    }
</style>
