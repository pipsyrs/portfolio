<?php

namespace Tests\Feature\Security;

use App\Livewire\Dashboard\Profile\Sessions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SessionListTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'database']);

        $this->owner = User::factory()->create(['password' => Hash::make('kata-sandi-kuat')]);
        $this->actingAs($this->owner);
    }

    private function seedSession(string $id, ?string $userId, string $agent, int $minutesAgo = 0): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => '203.0.113.7',
            'user_agent' => $agent,
            'payload' => '',
            'last_activity' => now()->subMinutes($minutesAgo)->getTimestamp(),
        ]);
    }

    public function test_it_lists_only_sessions_belonging_to_the_signed_in_user(): void
    {
        $other = User::factory()->create();

        $this->seedSession('mine-1', $this->owner->id, 'Mozilla/5.0 (Windows NT 10.0) Chrome/120');
        $this->seedSession('theirs', $other->id, 'Mozilla/5.0 (Macintosh) Safari/17');

        Livewire::test(Sessions::class)
            ->assertSee('Chrome')
            ->assertSee('Windows')
            ->assertDontSee('Safari');
    }

    public function test_it_marks_the_current_device(): void
    {
        $this->seedSession(session()->getId(), $this->owner->id, 'Mozilla/5.0 (Windows NT 10.0) Firefox/121');

        Livewire::test(Sessions::class)->assertSee('PERANGKAT INI');
    }

    public function test_another_session_can_be_revoked(): void
    {
        $this->seedSession('lain', $this->owner->id, 'Mozilla/5.0 (Linux; Android 14) Chrome/120');

        Livewire::test(Sessions::class)
            ->call('confirmRevoke', 'lain')
            ->call('revoke');

        $this->assertDatabaseMissing('sessions', ['id' => 'lain']);
    }

    public function test_the_current_session_cannot_be_revoked_from_the_list(): void
    {
        $id = session()->getId();
        $this->seedSession($id, $this->owner->id, 'Mozilla/5.0 (Windows NT 10.0) Chrome/120');

        Livewire::test(Sessions::class)
            ->call('confirmRevoke', $id)
            ->call('revoke');

        $this->assertDatabaseHas('sessions', ['id' => $id]);
    }

    public function test_revoking_all_others_requires_the_correct_password(): void
    {
        $this->seedSession(session()->getId(), $this->owner->id, 'Chrome');
        $this->seedSession('lain-1', $this->owner->id, 'Firefox');
        $this->seedSession('lain-2', $this->owner->id, 'Safari');

        Livewire::test(Sessions::class)
            ->call('askPassword')
            ->set('password', 'kata-sandi-salah')
            ->call('revokeOthers')
            ->assertHasErrors('password');

        $this->assertDatabaseHas('sessions', ['id' => 'lain-1']);

        Livewire::test(Sessions::class)
            ->call('askPassword')
            ->set('password', 'kata-sandi-kuat')
            ->call('revokeOthers')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['id' => 'lain-1']);
        $this->assertDatabaseMissing('sessions', ['id' => 'lain-2']);
        $this->assertDatabaseHas('sessions', ['id' => session()->getId()]);
    }
}
