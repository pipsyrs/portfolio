<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Akses tunggal ke tabel pengaturan key/value.
 *
 * Yang di-cache adalah nilai MENTAH seperti tersimpan di basis data, jadi
 * rahasia tetap berbentuk ciphertext selama berada di cache; dekripsi hanya
 * terjadi saat key-nya benar-benar dibaca.
 */
class Settings
{
    /** @var array<string,string|null>|null */
    private ?array $raw = null;

    /**
     * @return array<string,array{cast:string,default:mixed,secret?:bool}>
     */
    public function schema(): array
    {
        return config('settings.schema', []);
    }

    public function defined(string $key): bool
    {
        return array_key_exists($key, $this->schema());
    }

    public function isSecret(string $key): bool
    {
        return (bool) ($this->schema()[$key]['secret'] ?? false);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $meta = $this->schema()[$key] ?? null;

        if ($meta === null) {
            return $default;
        }

        $raw = $this->raw()[$key] ?? null;

        if ($raw === null) {
            return $default ?? $meta['default'] ?? null;
        }

        if ($this->isSecret($key)) {
            $raw = $this->decrypt($raw);

            if ($raw === null) {
                return $default ?? $meta['default'] ?? null;
            }
        }

        return $this->cast($raw, $meta['cast'] ?? 'string');
    }

    /**
     * Nilai semua key yang terdaftar, rahasia sudah didekripsi.
     *
     * @return array<string,mixed>
     */
    public function all(): array
    {
        $values = [];

        foreach (array_keys($this->schema()) as $key) {
            $values[$key] = $this->get($key);
        }

        return $values;
    }

    /**
     * Nilai semua key KECUALI yang ditandai rahasia. Dipakai saat pengaturan
     * perlu dikirim ke browser.
     *
     * @return array<string,mixed>
     */
    public function visible(): array
    {
        return array_diff_key($this->all(), array_flip($this->secretKeys()));
    }

    /** @return list<string> */
    public function secretKeys(): array
    {
        return array_keys(array_filter($this->schema(), fn ($meta) => $meta['secret'] ?? false));
    }

    /** Key rahasia ini sudah terisi (tanpa membocorkan nilainya). */
    public function hasSecret(string $key): bool
    {
        return $this->isSecret($key) && filled($this->raw()[$key] ?? null);
    }

    /**
     * @param  string|array<string,mixed>  $key
     */
    public function set(string|array $key, mixed $value = null): void
    {
        $pairs = is_array($key) ? $key : [$key => $value];

        foreach ($pairs as $name => $item) {
            // Key di luar skema diabaikan: form tidak boleh menulis kolom bebas.
            if (! $this->defined($name)) {
                continue;
            }

            $stored = $this->serialize($item, $this->schema()[$name]['cast'] ?? 'string');

            if ($stored !== null && $this->isSecret($name)) {
                $stored = Crypt::encryptString($stored);
            }

            Setting::query()->updateOrCreate(['key' => $name], ['value' => $stored]);
        }

        $this->flush();
    }

    public function forget(string $key): void
    {
        Setting::query()->where('key', $key)->delete();

        $this->flush();
    }

    public function flush(): void
    {
        $this->raw = null;

        cache()->forget(config('settings.cache_key', 'portfolio.settings'));
    }

    /**
     * Warna ini disuntikkan mentah ke dalam blok <style>, jadi hanya hex yang
     * sudah tervalidasi yang boleh lolos.
     */
    public function color(): string
    {
        $color = (string) $this->get('app_color');

        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#38bdf8';
    }

    /** Bagian landing page yang belum pernah diatur dianggap aktif. */
    public function sectionEnabled(string $section): bool
    {
        $sections = $this->get('landing_sections');

        if (! is_array($sections) || ! array_key_exists($section, $sections)) {
            return true;
        }

        return (bool) $sections[$section];
    }

    public function recaptchaReady(): bool
    {
        return (bool) $this->get('recaptcha_enabled')
            && $this->hasSecret('recaptcha_site_key')
            && $this->hasSecret('recaptcha_secret');
    }

    /**
     * @return array<string,string|null>
     */
    private function raw(): array
    {
        if ($this->raw !== null) {
            return $this->raw;
        }

        return $this->raw = cache()->remember(
            config('settings.cache_key', 'portfolio.settings'),
            now()->addSeconds((int) config('settings.cache_ttl', 3600)),
            function (): array {
                // Saat instalasi pertama atau `config:cache` di CI, tabelnya
                // mungkin belum ada. Itu tidak boleh menjatuhkan aplikasi.
                try {
                    if (! Schema::hasTable('settings')) {
                        return [];
                    }

                    return Setting::query()->pluck('value', 'key')->all();
                } catch (Throwable) {
                    return [];
                }
            },
        );
    }

    private function decrypt(string $value): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // APP_KEY berganti tanpa rekey: perlakukan sebagai belum diisi
            // supaya aplikasi jatuh ke nilai .env, bukan error.
            return null;
        }
    }

    private function cast(string $value, string $type): mixed
    {
        return match ($type) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOL),
            'int' => (int) $value,
            'float' => (float) $value,
            'array' => json_decode($value, true) ?: [],
            default => $value,
        };
    }

    private function serialize(mixed $value, string $type): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            'bool' => $value ? '1' : '0',
            'array' => json_encode(is_array($value) ? $value : [$value]),
            default => (string) $value,
        };
    }
}
