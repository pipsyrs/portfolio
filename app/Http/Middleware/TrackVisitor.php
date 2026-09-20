<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menggantikan penulisan baris visitor pada setiap hit di routes/web.php.
 * Bot diabaikan dan satu pengunjung hanya dicatat sekali per hari, supaya
 * tabel tidak membengkak dan tidak bisa dijadikan alat pembanjiran database.
 */
class TrackVisitor
{
    private const BOT_SIGNATURES = [
        'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'headless',
        'python-requests', 'go-http-client', 'postman', 'lighthouse',
        'monitor', 'pingdom', 'uptime', 'facebookexternalhit', 'preview',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $request->ajax()) {
            return $response;
        }

        try {
            if (! settings('visitor_tracking_enabled')) {
                return $response;
            }

            $userAgent = (string) $request->userAgent();
            $today = now()->toDateString();
            $sessionKey = 'visitor_tracked_'.$today;

            if ($request->session()->has($sessionKey)) {
                return $response;
            }

            if ($this->looksLikeBot($userAgent)) {
                $request->session()->put($sessionKey, true);

                return $response;
            }

            $ip = get_real_ip();

            // Pagar kedua untuk klien tanpa cookie: satu IP satu baris per hari.
            $alreadyLogged = Visitor::query()
                ->where('ip_address', $ip)
                ->where('visited_date', $today)
                ->exists();

            if (! $alreadyLogged) {
                $visitor = Visitor::create([
                    'ip_address' => $ip,
                    'visited_date' => $today,
                    'user_agent' => Str::limit($userAgent, 500, ''),
                    'is_bot' => false,
                ]);

                $this->resolveLocation($visitor, $ip);
            }

            $request->session()->put($sessionKey, true);
        } catch (\Throwable $e) {
            // Pencatatan pengunjung tidak boleh menjatuhkan halaman publik.
            Log::warning('Visitor tracking failed', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    /**
     * Geolokasi diselesaikan sekali per kunjungan dan disimpan di baris itu
     * sendiri. Dijalankan setelah respons dikirim supaya panggilan HTTP ke
     * layanan eksternal tidak menambah waktu muat halaman publik, dan agar
     * tabel pengunjung tidak lagi memanggil API per baris saat dirender.
     */
    private function resolveLocation(Visitor $visitor, string $ip): void
    {
        dispatch(function () use ($visitor, $ip) {
            try {
                $location = get_location_from_ip($ip);

                if (blank($location) || in_array($location, ['-', 'Lokasi tidak diketahui'], true)) {
                    return;
                }

                $parts = array_map('trim', explode(',', $location));

                $visitor->forceFill([
                    'city' => count($parts) > 1 ? $parts[0] : null,
                    'country' => end($parts) ?: null,
                ])->save();
            } catch (\Throwable $e) {
                Log::warning('Visitor geolocation failed', ['error' => $e->getMessage()]);
            }
        })->afterResponse();
    }

    private function looksLikeBot(string $userAgent): bool
    {
        if ($userAgent === '') {
            return true;
        }

        return Str::contains(Str::lower($userAgent), self::BOT_SIGNATURES);
    }
}
