<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifikasi reCAPTCHA v3 tanpa paket pihak ketiga.
 *
 * Hanya aktif bila dinyalakan DAN kedua kuncinya terisi, sehingga konfigurasi
 * yang setengah jadi tidak pernah mengunci pemilik di luar dashboard-nya sendiri.
 */
class RecaptchaService
{
    private const ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

    public function isEnabled(): bool
    {
        return (bool) config('services.recaptcha.enabled')
            && filled(config('services.recaptcha.site_key'))
            && filled(config('services.recaptcha.secret'));
    }

    public function siteKey(): ?string
    {
        return $this->isEnabled() ? (string) config('services.recaptcha.site_key') : null;
    }

    /**
     * @return array{ok:bool,score:float|null,message:string}
     */
    public function check(?string $token, string $action = 'submit'): array
    {
        if (! $this->isEnabled()) {
            return ['ok' => true, 'score' => null, 'message' => 'reCAPTCHA tidak aktif.'];
        }

        if (blank($token)) {
            return ['ok' => false, 'score' => null, 'message' => 'Token reCAPTCHA tidak ditemukan. Muat ulang halaman.'];
        }

        try {
            $response = Http::asForm()->timeout(8)->post(self::ENDPOINT, [
                'secret' => config('services.recaptcha.secret'),
                'response' => $token,
                'remoteip' => get_real_ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA request failed', ['error' => $e->getMessage()]);

            // Layanan Google tidak terjangkau. Menolak semua orang di sini akan
            // mengunci pemilik dari dashboard-nya sendiri, jadi dilewatkan
            // dengan catatan — pembatas laju login tetap berlaku.
            return ['ok' => true, 'score' => null, 'message' => 'Verifikasi reCAPTCHA dilewati: layanan tidak terjangkau.'];
        }

        $data = $response->json();

        if (! is_array($data) || ! ($data['success'] ?? false)) {
            $codes = implode(', ', $data['error-codes'] ?? ['tidak diketahui']);

            return ['ok' => false, 'score' => null, 'message' => 'Verifikasi reCAPTCHA gagal ('.$codes.').'];
        }

        $score = (float) ($data['score'] ?? 0);
        $threshold = (float) config('services.recaptcha.threshold', 0.5);
        $returnedAction = $data['action'] ?? $action;

        if ($returnedAction !== $action) {
            return ['ok' => false, 'score' => $score, 'message' => 'Aksi reCAPTCHA tidak cocok.'];
        }

        return $score >= $threshold
            ? ['ok' => true, 'score' => $score, 'message' => 'Terverifikasi (skor '.$score.').']
            : ['ok' => false, 'score' => $score, 'message' => 'Skor reCAPTCHA terlalu rendah ('.$score.' < '.$threshold.').'];
    }
}
