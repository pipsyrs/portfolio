<?php

namespace Tests\Feature\Security;

use App\Livewire\Dashboard\Auth\Login;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class RecaptchaTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create([
            'email' => 'owner@contoh.test',
            'password' => Hash::make('kata-sandi-kuat'),
        ]);
    }

    private function enableRecaptcha(float $threshold = 0.5): void
    {
        config([
            'services.recaptcha.enabled' => true,
            'services.recaptcha.site_key' => 'site-key-uji',
            'services.recaptcha.secret' => 'secret-uji',
            'services.recaptcha.threshold' => $threshold,
        ]);
    }

    private function googleReturns(array $body): void
    {
        Http::fake(['www.google.com/recaptcha/*' => Http::response($body)]);
    }

    public function test_it_stays_disabled_until_both_keys_are_present(): void
    {
        config([
            'services.recaptcha.enabled' => true,
            'services.recaptcha.site_key' => 'site-key-uji',
            'services.recaptcha.secret' => null,
        ]);

        $this->assertFalse(app(RecaptchaService::class)->isEnabled());
        $this->assertNull(app(RecaptchaService::class)->siteKey());
    }

    public function test_a_disabled_service_passes_everything_through(): void
    {
        config(['services.recaptcha.enabled' => false]);

        $result = app(RecaptchaService::class)->check(null);

        $this->assertTrue($result['ok']);
    }

    public function test_a_score_below_the_threshold_is_rejected(): void
    {
        $this->enableRecaptcha(0.5);
        $this->googleReturns(['success' => true, 'score' => 0.2, 'action' => 'login']);

        $result = app(RecaptchaService::class)->check('token', 'login');

        $this->assertFalse($result['ok']);
        $this->assertSame(0.2, $result['score']);
    }

    public function test_a_score_above_the_threshold_passes(): void
    {
        $this->enableRecaptcha(0.5);
        $this->googleReturns(['success' => true, 'score' => 0.9, 'action' => 'login']);

        $this->assertTrue(app(RecaptchaService::class)->check('token', 'login')['ok']);
    }

    public function test_a_mismatched_action_is_rejected(): void
    {
        $this->enableRecaptcha();
        $this->googleReturns(['success' => true, 'score' => 0.9, 'action' => 'contact']);

        // Token dari formulir lain tidak boleh dipakai ulang di login.
        $this->assertFalse(app(RecaptchaService::class)->check('token', 'login')['ok']);
    }

    public function test_a_missing_token_is_rejected(): void
    {
        $this->enableRecaptcha();

        $this->assertFalse(app(RecaptchaService::class)->check('', 'login')['ok']);
    }

    public function test_an_unreachable_google_does_not_lock_the_owner_out(): void
    {
        $this->enableRecaptcha();
        Http::fake(fn () => throw new \RuntimeException('jaringan mati'));

        $result = app(RecaptchaService::class)->check('token', 'login');

        $this->assertTrue($result['ok'], 'Layanan yang mati tidak boleh mengunci pemilik.');
    }

    public function test_login_is_blocked_when_the_score_is_too_low(): void
    {
        $this->owner();
        $this->enableRecaptcha(0.5);
        $this->googleReturns(['success' => true, 'score' => 0.1, 'action' => 'login']);

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->set('recaptchaToken', 'token-bot')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_succeeds_when_the_score_is_high_enough(): void
    {
        $owner = $this->owner();
        $this->enableRecaptcha(0.5);
        $this->googleReturns(['success' => true, 'score' => 0.9, 'action' => 'login']);

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->set('recaptchaToken', 'token-manusia')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard.home'));

        $this->assertAuthenticatedAs($owner);
    }

    public function test_recaptcha_runs_before_credentials_are_checked(): void
    {
        $this->owner();
        $this->enableRecaptcha();
        $this->googleReturns(['success' => true, 'score' => 0.1, 'action' => 'login']);

        // Kata sandi benar, tapi skor rendah: tetap ditolak tanpa sesi terbentuk.
        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->set('recaptchaToken', 'token-bot')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }
}
