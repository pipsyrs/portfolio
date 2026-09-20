<?php

namespace Tests\Feature\Security;

use App\Livewire\Dashboard\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create([
            'email' => 'owner@contoh.test',
            'password' => Hash::make('kata-sandi-kuat'),
        ]);
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('login:owner@contoh.test|127.0.0.1');

        parent::tearDown();
    }

    public function test_owner_can_sign_in_with_valid_credentials(): void
    {
        $this->owner();

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard.home'));

        $this->assertAuthenticated();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->owner();

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-salah')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_it_locks_out_after_five_failed_attempts(): void
    {
        $this->owner();

        $component = Livewire::test(Login::class)->set('email', 'owner@contoh.test');

        // Komponen mengosongkan field password setiap kali gagal, jadi tiap
        // percobaan harus mengisinya lagi — persis seperti penyerang sungguhan.
        for ($i = 0; $i < 5; $i++) {
            $component->set('password', 'kata-sandi-salah')->call('login');
        }

        // Percobaan ke-6 harus ditolak oleh pembatas laju, bukan oleh
        // pemeriksaan kredensial — bahkan bila kata sandinya benar.
        $component->set('password', 'kata-sandi-kuat')->call('login');

        $this->assertGuest();

        $errors = $component->errors()->get('email');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Terlalu banyak percobaan', $errors[0]);
    }

    public function test_honeypot_field_blocks_automated_submissions(): void
    {
        $this->owner();

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->set('website', 'https://spam.test')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_non_owner_account_cannot_sign_in(): void
    {
        $this->owner();

        User::factory()->create([
            'email' => 'penyusup@contoh.test',
            'password' => Hash::make('kata-sandi-kuat'),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'penyusup@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_short_passwords_are_rejected_before_hitting_the_database(): void
    {
        $this->owner();

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', '123')
            ->call('login')
            ->assertHasErrors(['password' => 'min']);
    }
}
