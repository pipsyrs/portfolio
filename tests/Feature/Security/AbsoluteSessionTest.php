<?php

namespace Tests\Feature\Security;

use App\Http\Middleware\EnforceAbsoluteSessionLifetime as AbsoluteLifetime;
use App\Livewire\Dashboard\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AbsoluteSessionTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create([
            'email' => 'owner@contoh.test',
            'password' => Hash::make('kata-sandi-kuat'),
        ]);
    }

    public function test_login_stamps_the_session_start_time(): void
    {
        $this->owner();

        Livewire::test(Login::class)
            ->set('email', 'owner@contoh.test')
            ->set('password', 'kata-sandi-kuat')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertIsInt(session(AbsoluteLifetime::KEY));
    }

    public function test_an_active_session_still_expires_at_the_absolute_limit(): void
    {
        config(['session.absolute_lifetime' => 120]);

        $owner = $this->owner();

        // Sesi yang dimulai 121 menit lalu, walau baru saja dipakai.
        $this->actingAs($owner)
            ->withSession([AbsoluteLifetime::KEY => now()->subMinutes(121)->getTimestamp()])
            ->get(route('dashboard.home'))
            ->assertRedirect(route('dashboard.login'))
            ->assertSessionHas('session-expired');

        $this->assertGuest();
    }

    public function test_a_session_within_the_limit_is_untouched(): void
    {
        config(['session.absolute_lifetime' => 120]);

        $owner = $this->owner();

        $this->actingAs($owner)
            ->withSession([AbsoluteLifetime::KEY => now()->subMinutes(119)->getTimestamp()])
            ->get(route('dashboard.home'))
            ->assertOk();

        $this->assertAuthenticated();
    }

    public function test_a_session_without_a_stamp_is_adopted_rather_than_dropped(): void
    {
        config(['session.absolute_lifetime' => 120]);

        // Sesi yang sudah berjalan sebelum fitur ini dipasang.
        $this->actingAs($this->owner())
            ->get(route('dashboard.home'))
            ->assertOk();

        $this->assertIsInt(session(AbsoluteLifetime::KEY));
    }

    public function test_the_limit_can_be_disabled(): void
    {
        config(['session.absolute_lifetime' => 0]);

        $this->actingAs($this->owner())
            ->withSession([AbsoluteLifetime::KEY => now()->subYear()->getTimestamp()])
            ->get(route('dashboard.home'))
            ->assertOk();
    }
}
