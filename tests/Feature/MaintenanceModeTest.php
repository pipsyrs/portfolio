<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        settings()->set(['app_name' => 'Uji', 'maintenance_mode' => true]);
    }

    public function test_visitors_see_the_maintenance_page(): void
    {
        $this->get(route('index'))->assertStatus(503);
    }

    public function test_the_login_page_stays_reachable(): void
    {
        $this->get(route('dashboard.login'))->assertOk();
    }

    public function test_livewire_requests_from_the_login_page_are_not_blocked(): void
    {
        // Menembak endpoint Livewire lewat HTTP supaya middleware ikut jalan —
        // memanggil komponen langsung akan melewatinya dan bug ini lolos.
        $response = $this->postJson(route('default-livewire.update'), [
            'components' => [[
                'snapshot' => json_encode([
                    'data' => [],
                    'memo' => ['id' => 'x', 'name' => 'dashboard.auth.login', 'path' => 'pipspanel/login', 'method' => 'GET', 'children' => [], 'scripts' => [], 'assets' => [], 'errors' => [], 'locale' => 'id'],
                    'checksum' => 'x',
                ]),
                'updates' => [],
                'calls' => [],
            ]],
        ], ['X-Livewire' => 'true']);

        // Checksum sengaja tidak valid, jadi Livewire akan menolaknya sendiri.
        // Yang diuji: middleware maintenance tidak memotongnya lebih dulu.
        $this->assertNotSame(503, $response->status());
    }

    public function test_livewire_requests_from_the_landing_page_stay_blocked(): void
    {
        // Formulir kontak tidak boleh bisa ditembak dari halaman yang terlanjur
        // terbuka, walau pemanggilnya memalsukan path asal ke luar dashboard.
        $this->postJson(route('default-livewire.update'), [
            'components' => [[
                'snapshot' => json_encode([
                    'data' => [],
                    'memo' => ['id' => 'x', 'name' => 'landing', 'path' => '/', 'method' => 'GET'],
                    'checksum' => 'x',
                ]),
                'updates' => [],
                'calls' => [],
            ]],
        ], ['X-Livewire' => 'true'])->assertStatus(503);
    }

    public function test_health_check_is_not_blocked(): void
    {
        $this->get('/up')->assertOk();
    }
}
