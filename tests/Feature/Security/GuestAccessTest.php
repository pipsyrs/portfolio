<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function dashboardRoutes(): array
    {
        return [
            'beranda' => ['dashboard.home'],
            'projects' => ['dashboard.projects'],
            'buat project' => ['dashboard.projects.create'],
            'tech stack' => ['dashboard.tech-stacks'],
            'spesialisasi' => ['dashboard.specializations'],
            'kontak' => ['dashboard.contacts'],
            'notifikasi' => ['dashboard.notifications'],
            'profil' => ['dashboard.profile'],
            'pengaturan' => ['dashboard.settings'],
            'backup' => ['dashboard.backup'],
        ];
    }

    #[DataProvider('dashboardRoutes')]
    public function test_guests_are_redirected_to_login(string $route): void
    {
        $this->get(route($route))->assertRedirect(route('dashboard.login'));
    }

    public function test_owner_can_open_every_dashboard_page(): void
    {
        $owner = User::factory()->create();

        foreach (array_column(self::dashboardRoutes(), 0) as $route) {
            $this->actingAs($owner)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_a_second_account_is_rejected_as_not_owner(): void
    {
        // Pemilik adalah user dengan id terkecil; akun lain harus ditolak
        // walaupun berhasil melewati middleware auth.
        User::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->get(route('dashboard.home'))
            ->assertForbidden();
    }

    public function test_logout_requires_a_post_request(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->get(route('dashboard.logout'))->assertStatus(405);

        $this->actingAs($owner)
            ->post(route('dashboard.logout'))
            ->assertRedirect(route('dashboard.login'));

        $this->assertGuest();
    }
}
