<?php

namespace Tests\Feature\Security;

use App\Livewire\Dashboard\Auth\Login;
use App\Livewire\Dashboard\Profile\TwoFactor as TwoFactorPanel;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create([
            'email' => 'owner@contoh.test',
            'password' => Hash::make('kata-sandi-kuat'),
        ]);
    }

    private function enable(User $user): string
    {
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['AAAAAAAA-BBBBBBBB', 'CCCCCCCC-DDDDDDDD'],
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $secret;
    }

    private function currentCode(string $secret): string
    {
        return app(Google2FA::class)->getCurrentOtp($secret);
    }

    public function test_the_secret_is_stored_encrypted(): void
    {
        $user = $this->owner();
        $secret = $this->enable($user);

        $raw = DB::table('users')
            ->where('id', $user->id)
            ->value('two_factor_secret');

        $this->assertNotSame($secret, $raw, 'Secret tidak boleh tersimpan apa adanya.');
        $this->assertSame($secret, $user->fresh()->two_factor_secret);
    }

    public function test_setup_requires_a_valid_code_before_it_activates(): void
    {
        $user = $this->owner();
        $this->actingAs($user);

        $component = Livewire::test(TwoFactorPanel::class)->call('startSetup');
        $secret = $component->get('pendingSecret');

        $component->set('code', '000000')->call('confirm')->assertHasErrors('code');
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());

        $component->set('code', $this->currentCode($secret))->call('confirm')->assertHasNoErrors();
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
        $this->assertCount(8, $user->fresh()->recoveryCodes());
    }

    public function test_login_stops_at_the_second_step_when_2fa_is_enabled(): void
    {
        $user = $this->owner();
        $this->enable($user);

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->call('login')
            ->assertHasNoErrors()
            ->assertSet('awaitingTwoFactor', true)
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_a_valid_totp_completes_the_login(): void
    {
        $user = $this->owner();
        $secret = $this->enable($user);

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->call('login')
            ->set('twoFactorCode', $this->currentCode($secret))
            ->call('verifyTwoFactor')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard.home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_totp_keeps_the_user_out(): void
    {
        $user = $this->owner();
        $this->enable($user);

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->call('login')
            ->set('twoFactorCode', '123456')
            ->call('verifyTwoFactor')
            ->assertHasErrors('twoFactorCode');

        $this->assertGuest();
    }

    public function test_a_recovery_code_works_once_and_is_then_burned(): void
    {
        $user = $this->owner();
        $this->enable($user);

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->call('login')
            ->call('toggleRecoveryCode')
            ->set('twoFactorCode', 'AAAAAAAA-BBBBBBBB')
            ->call('verifyTwoFactor')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(['CCCCCCCC-DDDDDDDD'], $user->fresh()->recoveryCodes());
    }

    public function test_disabling_requires_the_password(): void
    {
        $user = $this->owner();
        $this->enable($user);
        $this->actingAs($user);

        Livewire::test(TwoFactorPanel::class)
            ->call('askDisable')
            ->set('password', 'salah')
            ->call('disable')
            ->assertHasErrors('password');

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        Livewire::test(TwoFactorPanel::class)
            ->call('askDisable')
            ->set('password', 'kata-sandi-kuat')
            ->call('disable')
            ->assertHasNoErrors();

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }
}
