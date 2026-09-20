<?php

namespace Tests\Feature\Security;

use App\Livewire\Dashboard\Settings\Integrations;
use App\Models\User;
use App\Providers\SettingsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class IntegrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        settings()->set(['app_name' => 'Uji', 'app_name_short' => 'UJI', 'app_color' => '#38bdf8']);
        $this->actingAs(User::factory()->create());
    }

    /** Nilai mentah satu key, langsung dari basis data tanpa lewat repository. */
    private function raw(string $key): ?string
    {
        return DB::table('settings')->where('key', $key)->value('value');
    }

    public function test_secrets_are_stored_encrypted(): void
    {
        Livewire::test(Integrations::class, ['section' => 'email'])
            ->set('secrets.mail_password', 'rahasia-smtp-123')
            ->call('saveMail')
            ->assertHasNoErrors();

        $raw = $this->raw('mail_password');

        $this->assertNotSame('rahasia-smtp-123', $raw, 'Rahasia tidak boleh tersimpan apa adanya.');
        $this->assertStringNotContainsString('rahasia-smtp-123', (string) $raw);
        $this->assertSame('rahasia-smtp-123', settings('mail_password'));
    }

    public function test_a_stored_secret_is_never_sent_to_the_browser(): void
    {
        settings()->set('mail_password', 'rahasia-smtp-123');

        Livewire::test(Integrations::class, ['section' => 'email'])
            ->assertSet('secrets.mail_password', '')
            ->assertDontSee('rahasia-smtp-123')
            ->assertSee('TERSIMPAN');
    }

    public function test_an_empty_secret_field_keeps_the_previous_value(): void
    {
        settings()->set('mail_password', 'lama');

        Livewire::test(Integrations::class, ['section' => 'email'])
            ->set('mail_host', 'smtp.contoh.test')
            ->set('secrets.mail_password', '')
            ->call('saveMail')
            ->assertHasNoErrors();

        $this->assertSame('lama', settings('mail_password'));
        $this->assertSame('smtp.contoh.test', settings('mail_host'));
    }

    public function test_a_secret_can_be_cleared_back_to_the_env_fallback(): void
    {
        settings()->set('deepl_api_key', 'kunci-lama');

        Livewire::test(Integrations::class, ['section' => 'integrasi'])
            ->call('confirmClearSecret', 'deepl_api_key')
            ->call('clearSecret');

        $this->assertNull(settings('deepl_api_key'));
    }

    public function test_clear_secret_refuses_fields_outside_the_allowlist(): void
    {
        Livewire::test(Integrations::class, ['section' => 'integrasi'])
            ->call('confirmClearSecret', 'password')
            ->assertSet('clearingSecret', null)
            ->call('clearSecret');

        // Key di luar daftar rahasia tidak boleh tersentuh sama sekali.
        $this->assertNotNull(auth()->user()->fresh()->password);
    }

    public function test_only_keys_in_the_schema_can_be_written(): void
    {
        settings()->set(['kolom_liar' => 'nilai', 'app_name' => 'Tetap Ditulis']);

        $this->assertNull($this->raw('kolom_liar'));
        $this->assertSame('Tetap Ditulis', settings('app_name'));
    }

    public function test_the_database_panel_is_read_only_and_shows_no_password(): void
    {
        $component = Livewire::test(Integrations::class, ['section' => 'database']);

        $info = $component->instance()->databaseInfo();

        $this->assertArrayHasKey('Database', $info);
        $this->assertArrayNotHasKey('Password', $info);
        $this->assertArrayNotHasKey('Kata sandi', $info);
    }

    public function test_settings_override_mail_config_with_env_as_fallback(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'dari-env.test']);

        settings()->set([
            'mail_mailer' => 'smtp',
            'mail_host' => 'dari-pengaturan.test',
            'mail_from_address' => 'kirim@contoh.test',
        ]);

        // Provider dilewati di lingkungan pengujian, jadi dijalankan manual.
        (new SettingsServiceProvider($this->app))->applyRuntimeConfig();

        $this->assertSame('dari-pengaturan.test', config('mail.mailers.smtp.host'));
        $this->assertSame('kirim@contoh.test', config('mail.from.address'));
    }

    public function test_blank_settings_do_not_overwrite_env_values(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'dari-env.test']);

        (new SettingsServiceProvider($this->app))->applyRuntimeConfig();

        $this->assertSame('dari-env.test', config('mail.mailers.smtp.host'));
    }

    public function test_a_guest_cannot_touch_integration_settings(): void
    {
        auth()->logout();

        $this->get(route('dashboard.settings'))->assertRedirect(route('dashboard.login'));
    }
}
