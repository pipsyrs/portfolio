<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['name' => 'Pemilik Uji', 'specialis' => 'Fullstack Developer']);
        settings()->set(['app_name' => 'Portfolio Uji', 'app_name_short' => 'UJI', 'app_color' => '#38bdf8']);
    }

    public function test_the_landing_page_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Pemilik Uji', false);
    }

    public function test_it_records_one_visitor_per_session_per_day(): void
    {
        $this->get('/')->assertOk();
        $this->get('/')->assertOk();

        // Kunjungan kedua dalam sesi yang sama tidak boleh menambah baris.
        $this->assertSame(1, Visitor::count());
    }

    public function test_it_ignores_bot_user_agents(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')
            ->get('/')
            ->assertOk();

        $this->assertSame(0, Visitor::count());
    }

    public function test_it_skips_tracking_when_disabled_in_settings(): void
    {
        settings()->set('visitor_tracking_enabled', false);

        $this->get('/')->assertOk();

        $this->assertSame(0, Visitor::count());
    }

    public function test_it_stores_the_resolved_country_for_a_visitor(): void
    {
        // Layanan geolokasi eksternal dipalsukan supaya test tidak menyentuh
        // jaringan; yang diuji adalah hasilnya benar-benar tersimpan di baris.
        Http::fake([
            'ip-api.com/*' => Http::response([
                'status' => 'success',
                'city' => 'Makassar',
                'regionName' => 'Sulawesi Selatan',
                'country' => 'Indonesia',
            ]),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '114.125.1.1'])
            ->get('/')
            ->assertOk();

        $visitor = Visitor::first();

        $this->assertNotNull($visitor);
        $this->assertSame('Indonesia', $visitor->country);
        $this->assertSame('Makassar', $visitor->city);
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
