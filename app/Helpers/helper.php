<?php

use App\Support\Settings;
use DeepL\Translator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;

if (! function_exists('safe_image_url')) {
    function safe_image_url($path = null, $folder = null)
    {
        $default = asset('images/placeholder.jpg');

        if (empty($path)) {
            return $default;
        }

        // Jika URL lengkap, langsung return
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = ltrim($path, '/');

        // Jika path belum mengandung folder (hanya nama file), tambahkan folder
        if ($folder && ! str_contains($path, '/')) {
            $path = trim($folder, '/').'/'.$path;
        }
        // Jika path sudah mengandung '/', berarti sudah format folder/nama_file — pakai langsung

        // File lama / field yang masih memakai disk publik (mis. foto profil).
        $public = Storage::disk('public');
        if ($public->exists($path)) {
            return $public->url($path);
        }

        // Field yang diunggah ke disk privat (di luar webroot) — sajikan lewat
        // signed URL sementara supaya file tidak bisa ditebak/diakses langsung.
        $private = Storage::disk('private');
        if ($private->exists($path)) {
            return $private->temporaryUrl($path, now()->addHours(6));
        }

        return $default;
    }
}

if (! function_exists('safe_image_urls')) {
    function safe_image_urls($paths = null, $folder = null): array
    {
        if (empty($paths)) {
            return [];
        }

        // Jika masih string (misal data lama belum migrasi ke json)
        if (is_string($paths)) {
            $decoded = json_decode($paths, true);
            $paths = is_array($decoded) ? $decoded : [$paths];
        }

        if (! is_array($paths)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn ($path) => safe_image_url($path, $folder), $paths)
        ));
    }
}

if (! function_exists('format_bytes')) {
    function format_bytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}

if (! function_exists('get_real_ip')) {
    /**
     * Get real IP address from request, checking various headers
     * This is useful when behind proxies, load balancers, or CDN
     */
    function get_real_ip()
    {
        $request = request();

        // Try to get IP from various headers in order of priority
        $headers = [
            'HTTP_CF_CONNECTING_IP',    // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx proxy
            'HTTP_X_FORWARDED_FOR',      // Standard proxy header
            'HTTP_CLIENT_IP',            // Proxy
            'REMOTE_ADDR',                // Direct connection
        ];

        foreach ($headers as $header) {
            if ($ip = $request->server($header)) {
                // X-Forwarded-For can contain multiple IPs, get the first one
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }

                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        // Fallback to Laravel's ip() method
        return $request->ip();
    }
}

if (! function_exists('get_location_from_ip')) {
    /**
     * Get location information from IP address using ip-api.com (free, no API key needed)
     * Returns string like: "Jakarta, Indonesia" or "-" if failed
     */
    function get_location_from_ip($ip)
    {
        // Skip for localhost/private IPs
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) || empty($ip)) {
            return 'Localhost';
        }

        // Check if it's a private IP range
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return 'Private Network';
        }

        return cache()->remember("ip_location_{$ip}", now()->addDays(30), function () use ($ip) {
            try {
                // Use ip-api.com free service (limit: 45 requests per minute)
                // Added fields: city, regionName, country, timezone, isp
                $response = Illuminate\Support\Facades\Http::timeout(5)->get("https://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,timezone,isp");

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['status']) && $data['status'] === 'success') {
                        $city = $data['city'] ?? '';
                        $region = $data['regionName'] ?? '';
                        $country = $data['country'] ?? '';

                        // Format: City, Region, Country or City, Country
                        $location = [];
                        if ($city) {
                            $location[] = $city;
                        }
                        if ($region && $region !== $city) {
                            $location[] = $region;
                        }
                        if ($country) {
                            $location[] = $country;
                        }

                        return ! empty($location) ? implode(', ', $location) : '-';
                    }

                    // Log error jika ada
                    if (isset($data['message'])) {
                        Illuminate\Support\Facades\Log::warning('IP Location API Error', [
                            'ip' => $ip,
                            'message' => $data['message'],
                        ]);
                    }
                } else {
                    Illuminate\Support\Facades\Log::warning('IP Location API Failed', [
                        'ip' => $ip,
                        'status' => $response->status(),
                    ]);
                }
            } catch (Exception $e) {
                Illuminate\Support\Facades\Log::error('IP Location Exception', [
                    'ip' => $ip,
                    'error' => $e->getMessage(),
                ]);
            }

            return 'Lokasi tidak diketahui';
        });
    }
}

if (! function_exists('fa_access_token')) {
    function fa_access_token()
    {
        return cache()->remember('fa-access-token', 3500, function () {
            $response = Http::withToken(config('services.fontawesome.token'))
                ->post('https://api.fontawesome.com/token');

            $json = $response->json();

            if (! isset($json['access_token'])) {
                Log::error('FA token error', $json ?? []);

                return null;
            }

            return $json['access_token'];
        });
    }
}

if (! function_exists('translate_text')) {
    /**
     * Translate database text dynamically using the official DeepL API and cache it forever.
     *
     * @param  string|null  $text
     * @param  string|null  $targetLocale  Explicit target locale (defaults to the current app locale).
     */
    function translate_text($text, $targetLocale = null)
    {
        if (blank($text)) {
            return $text;
        }

        $locale = $targetLocale ?? app()->getLocale();

        $cacheKey = 'trans_'.md5($text).'_'.$locale;

        // Only successful translations are cached forever. A failed attempt
        // (e.g. a transient API error or quota issue) must NOT be written to
        // a permanent cache, otherwise the untranslated fallback gets stuck
        // there forever and the string never gets a real chance to translate
        // again.
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Skip re-attempting while a recent failure is in its cooldown
        // window, so an outage doesn't hammer the API on every request.
        if (Cache::has($cacheKey.'_cooldown')) {
            return $text;
        }

        $apiKey = config('services.deepl.key');

        if (blank($apiKey)) {
            Illuminate\Support\Facades\Log::warning('translate_text: DEEPL_API_KEY is not configured.');

            return $text;
        }

        // DeepL requires a regional variant for English as a target
        // language, and uses its own two-letter codes otherwise.
        $targetLang = match (strtolower($locale)) {
            'en' => 'EN-US',
            'id' => 'ID',
            default => strtoupper($locale),
        };

        try {
            $translator = new Translator($apiKey);

            $result = $translator->translateText($text, null, $targetLang);
            $translated = $result->text;

            if (blank($translated)) {
                return $text;
            }

            Cache::forever($cacheKey, $translated);

            return $translated;
        } catch (Exception $e) {
            // Briefly cache the failure so a rate-limit / outage doesn't
            // cause every single request to re-hammer the API, while still
            // allowing a retry soon after.
            Cache::put($cacheKey.'_cooldown', true, now()->addMinutes(2));

            Illuminate\Support\Facades\Log::warning('translate_text failed', [
                'locale' => $locale,
                'error' => $e->getMessage(),
            ]);

            return $text;
        }
    }
}

if (! function_exists('bt')) {
    /**
     * Bilingual static UI text. Renders BOTH the English and Indonesian variant
     * of a lang-file string inline (wrapped in .i18n-en / .i18n-id spans) so the
     * frontend can switch the visible language instantly with CSS, with no
     * server round-trip / page reload. Use as {!! bt('Key') !!}.
     */
    function bt(string $key): string
    {
        $en = $key; // In this project the English lang file is the identity map (key === source string).
        $id = Lang::has($key, 'id')
            ? Lang::get($key, [], 'id')
            : $key;

        return '<span class="i18n-en">'.e($en).'</span><span class="i18n-id">'.e($id).'</span>';
    }
}

if (! function_exists('bt_variant')) {
    /**
     * Single-locale variant of a static UI string (English key or Indonesian
     * lang-file lookup). Used for attributes like `placeholder` that can't hold
     * two visible spans — pair with the .i18n-placeholder JS in landing.blade.php
     * to still switch instantly on the client.
     */
    function bt_variant(string $key, string $locale): string
    {
        if ($locale === 'id') {
            return Lang::has($key, 'id')
                ? Lang::get($key, [], 'id')
                : $key;
        }

        return $key;
    }
}

if (! function_exists('bt_dynamic')) {
    /**
     * Bilingual dynamic (database-driven) text, machine-translated via
     * translate_text(). Renders both language variants inline, same idea as
     * bt() above, for content whose language can't be swapped client-side
     * (career descriptions, headlines, project descriptions, etc.).
     *
     * @param  bool  $html  Set true for already-sanitized rich text (skips escaping).
     */
    function bt_dynamic(?string $text, bool $html = false): string
    {
        if (blank($text)) {
            return '';
        }

        $en = translate_text($text, 'en');
        $id = translate_text($text, 'id');

        if (! $html) {
            $en = e($en);
            $id = e($id);
        }

        return '<span class="i18n-en">'.$en.'</span><span class="i18n-id">'.$id.'</span>';
    }
}

if (! function_exists('settings')) {
    /**
     * Akses pengaturan aplikasi dari mana saja.
     *
     *   settings()                    -> App\Support\Settings
     *   settings('app_name')          -> nilai satu key
     *   settings(['app_name' => 'X']) -> menyimpan
     */
    function settings(string|array|null $key = null, mixed $default = null): mixed
    {
        $settings = app(Settings::class);

        if ($key === null) {
            return $settings;
        }

        if (is_array($key)) {
            $settings->set($key);

            return $settings;
        }

        return $settings->get($key, $default);
    }
}
