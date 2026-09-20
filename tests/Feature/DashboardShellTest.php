<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Projects\Index as ProjectsIndex;
use App\Livewire\Dashboard\Settings\Edit as SettingsEdit;
use App\Models\Projects;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardShellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        settings()->set(['app_name' => 'Uji', 'app_name_short' => 'UJI', 'app_color' => '#38bdf8']);
        $this->actingAs(User::factory()->create());
    }

    public function test_the_shell_wires_separate_mobile_and_desktop_sidebar_state(): void
    {
        $html = $this->get(route('dashboard.home'))->assertOk()->getContent();

        // Drawer mobile dan rail desktop tetap state terpisah, tapi
        // digerakkan satu tombol yang sama.
        $this->assertStringContainsString('x-data="dashShell"', $html);
        $this->assertStringContainsString("{ 'is-collapsed': collapsed }", $html);
        $this->assertStringContainsString('toggleSidebar()', $html);
        $this->assertStringContainsString("open ? 'translate-x-0'", $html);

        // Hanya satu pemicu di topbar; tutup drawer ada di dalam sidebar.
        $this->assertSame(1, substr_count($html, 'toggleSidebar()'));
    }

    public function test_logout_is_behind_a_confirmation_dialog(): void
    {
        $html = $this->get(route('dashboard.home'))->assertOk()->getContent();

        $this->assertStringContainsString('confirm-logout', $html);
        $this->assertStringContainsString('Keluar dari dashboard?', $html);

        // Tidak boleh ada form logout yang bisa dikirim tanpa konfirmasi.
        $this->assertSame(
            1,
            substr_count($html, 'action="'.route('dashboard.logout').'"'),
            'Hanya dialog konfirmasi yang boleh memuat form logout.',
        );
    }

    public function test_delete_confirmation_renders_through_the_shared_dialog(): void
    {
        $project = Projects::create([
            'name' => 'Project Uji',
            'description' => '<p>Deskripsi.</p>',
            'image' => 'projects/contoh.webp',
            'url' => 'https://contoh.test',
        ]);

        Livewire::test(ProjectsIndex::class)
            ->assertDontSee('dash-dialog-panel', false)
            ->call('confirmDelete', $project->id)
            ->assertSee('dash-dialog-panel', false)
            ->assertSee('Hapus project ini?')
            ->call('cancelDelete')
            ->assertDontSee('dash-dialog-panel', false);
    }

    public function test_settings_toggles_carry_semantic_tones(): void
    {
        // Toggle sistem hanya dirender pada tab-nya sendiri.
        Livewire::test(SettingsEdit::class)
            ->call('setTab', 'sistem')
            ->assertSee('tone-warning', false)
            ->assertSee('tone-success', false);

        Livewire::test(SettingsEdit::class)
            ->call('setTab', 'tampilan')
            ->assertSee('tone-success', false);
    }
}
