<?php

namespace App\Providers;

use App\Support\Settings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Satu-satunya tempat pengaturan disambungkan ke aplikasi:
 *
 *  - repository di-bind sebagai singleton, jadi seluruh request memakai
 *    instance yang sama (helper `settings()`, facade Settings, injeksi tipe);
 *  - $settings tersedia di semua view tanpa perlu dikirim per controller;
 *  - konfigurasi runtime (mail, reCAPTCHA, API) ditimpa nilai dari dashboard,
 *    dengan .env sebagai cadangan bila key-nya kosong.
 *
 * Kredensial DATABASE tidak ikut ditimpa — koneksi harus sudah terbuka untuk
 * bisa membaca tabel settings.
 */
class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
    }

    public function boot(): void
    {
        // Repository-nya malas: tidak ada query sampai ada key yang dibaca.
        View::share('settings', $this->app->make(Settings::class));

        // Pengujian menyiapkan konfigurasinya sendiri; penerapan otomatis di
        // sini akan menimpa skenario uji.
        if ($this->app->runningUnitTests()) {
            return;
        }

        $this->applyRuntimeConfig();
    }

    /**
     * Dipisah dari boot() supaya bisa dipanggil langsung oleh pengujian.
     */
    public function applyRuntimeConfig(): void
    {
        $settings = $this->app->make(Settings::class);

        $this->applyMail($settings);
        $this->applyIntegrations($settings);
    }

    private function applyMail(Settings $settings): void
    {
        $mailer = $this->value($settings->get('mail_mailer')) ?? config('mail.default');

        $overrides = array_filter([
            'mail.default' => $this->value($settings->get('mail_mailer')),
            "mail.mailers.{$mailer}.host" => $this->value($settings->get('mail_host')),
            "mail.mailers.{$mailer}.port" => $settings->get('mail_port') ?: null,
            "mail.mailers.{$mailer}.username" => $this->value($settings->get('mail_username')),
            "mail.mailers.{$mailer}.password" => $this->value($settings->get('mail_password')),
            "mail.mailers.{$mailer}.encryption" => $this->value($settings->get('mail_encryption')),
            'mail.from.address' => $this->value($settings->get('mail_from_address')),
            'mail.from.name' => $this->value($settings->get('mail_from_name')),
        ], fn ($value) => $value !== null);

        if ($overrides !== []) {
            config($overrides);
        }
    }

    private function applyIntegrations(Settings $settings): void
    {
        $overrides = array_filter([
            'services.deepl.key' => $this->value($settings->get('deepl_api_key')),
            'services.fontawesome.token' => $this->value($settings->get('fontawesome_token')),
            'services.recaptcha.site_key' => $this->value($settings->get('recaptcha_site_key')),
            'services.recaptcha.secret' => $this->value($settings->get('recaptcha_secret')),
        ], fn ($value) => $value !== null);

        // Threshold dan sakelar selalu berasal dari basis data agar bisa
        // dimatikan seketika tanpa menyentuh server.
        $overrides['services.recaptcha.enabled'] = $settings->recaptchaReady();
        $overrides['services.recaptcha.threshold'] = $settings->get('recaptcha_threshold') ?: 0.5;

        config($overrides);
    }

    private function value(mixed $raw): ?string
    {
        $value = is_string($raw) ? trim($raw) : $raw;

        return blank($value) ? null : (string) $value;
    }
}
