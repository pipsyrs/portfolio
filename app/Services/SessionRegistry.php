<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Membaca tabel `sessions` untuk menampilkan perangkat mana saja yang sedang
 * memegang sesi. Hanya berfungsi bila SESSION_DRIVER=database.
 */
class SessionRegistry
{
    public function isSupported(): bool
    {
        return config('session.driver') === 'database';
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function forUser(string $userId, ?string $currentId = null): array
    {
        if (! $this->isSupported()) {
            return [];
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'is_current' => $currentId !== null && $row->id === $currentId,
                'ip' => $row->ip_address ?: '-',
                'device' => $this->describe($row->user_agent ?? ''),
                'last_active' => Carbon::createFromTimestamp($row->last_activity),
                'agent' => Str::limit((string) $row->user_agent, 160),
            ])
            ->all();
    }

    public function revoke(string $userId, string $sessionId): bool
    {
        if (! $this->isSupported()) {
            return false;
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $userId)
            ->where('id', $sessionId)
            ->delete() > 0;
    }

    public function revokeOthers(string $userId, string $currentId): int
    {
        if (! $this->isSupported()) {
            return 0;
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $userId)
            ->where('id', '!=', $currentId)
            ->delete();
    }

    /**
     * Pengenalan perangkat sederhana. Cukup untuk membedakan "ini saya" dari
     * "ini bukan saya", yang memang satu-satunya keputusan di halaman ini.
     *
     * @return array{browser:string,platform:string,icon:string}
     */
    public function describe(string $agent): array
    {
        $ua = Str::lower($agent);

        $browser = match (true) {
            str_contains($ua, 'edg/') => 'Edge',
            str_contains($ua, 'opr/') || str_contains($ua, 'opera') => 'Opera',
            str_contains($ua, 'firefox') => 'Firefox',
            str_contains($ua, 'chrome') => 'Chrome',
            str_contains($ua, 'safari') => 'Safari',
            $ua === '' => 'Tidak diketahui',
            default => 'Peramban lain',
        };

        $platform = match (true) {
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') => 'iOS',
            str_contains($ua, 'mac os') || str_contains($ua, 'macintosh') => 'macOS',
            str_contains($ua, 'linux') => 'Linux',
            default => 'Tidak diketahui',
        };

        $icon = match (true) {
            str_contains($ua, 'android') || str_contains($ua, 'iphone') => 'fa-solid fa-mobile-screen',
            str_contains($ua, 'ipad') || str_contains($ua, 'tablet') => 'fa-solid fa-tablet-screen-button',
            default => 'fa-solid fa-desktop',
        };

        return ['browser' => $browser, 'platform' => $platform, 'icon' => $icon];
    }
}
