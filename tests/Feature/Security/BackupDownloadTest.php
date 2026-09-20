<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BackupDownloadTest extends TestCase
{
    use RefreshDatabase;

    private string $filename = 'backup_2026-01-02_03-04-05.sql.gz';

    protected function setUp(): void
    {
        parent::setUp();

        $directory = storage_path('app/backups');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0750, true);
        }

        File::put($directory.DIRECTORY_SEPARATOR.$this->filename, gzencode('-- dump uji'));
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('app/backups/'.$this->filename));

        parent::tearDown();
    }

    private function signedUrl(string $file): string
    {
        return URL::temporarySignedRoute('dashboard.backup.download', now()->addMinutes(5), ['file' => $file]);
    }

    public function test_guests_cannot_download_a_backup_even_with_a_valid_signature(): void
    {
        $this->get($this->signedUrl($this->filename))
            ->assertRedirect(route('dashboard.login'));
    }

    public function test_owner_can_download_with_a_valid_signature(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get($this->signedUrl($this->filename))
            ->assertOk()
            ->assertDownload($this->filename);
    }

    public function test_an_unsigned_url_is_rejected(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get(route('dashboard.backup.download', ['file' => $this->filename]))
            ->assertForbidden();
    }

    public function test_a_tampered_signature_is_rejected(): void
    {
        $owner = User::factory()->create();

        $url = $this->signedUrl($this->filename);

        $this->actingAs($owner)
            ->get($url.'x')
            ->assertForbidden();
    }

    public function test_path_traversal_attempts_are_rejected(): void
    {
        $owner = User::factory()->create();

        foreach (['../../../.env', '..%2F..%2F.env', 'backup_2026-01-02_03-04-05.sql.gz/../../.env'] as $payload) {
            $response = $this->actingAs($owner)->get($this->signedUrl($payload));

            $this->assertContains(
                $response->getStatusCode(),
                [403, 404],
                'Payload traversal tidak ditolak: '.$payload,
            );
        }
    }

    public function test_filenames_outside_the_backup_naming_pattern_are_rejected(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->get($this->signedUrl('laravel.log'))
            ->assertNotFound();
    }
}
