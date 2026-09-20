<?php

namespace Tests\Feature\Security;

use App\Livewire\Dashboard\Projects\Form;
use App\Models\Projects;
use App\Models\Specializations;
use App\Models\TechStacks;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UploadValidationTest extends TestCase
{
    use RefreshDatabase;

    private function actAsOwner(): User
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        return $owner;
    }

    private function formWithValidFields()
    {
        $tech = TechStacks::create(['name' => 'Laravel', 'icon' => 'fa-brands fa-laravel']);
        $specialization = Specializations::create(['name' => 'Backend', 'icon' => 'fa-solid fa-server']);

        return Livewire::test(Form::class)
            ->set('name', 'Project Uji')
            ->set('description', '<p>Deskripsi yang cukup panjang untuk lolos validasi.</p>')
            ->set('url', 'https://contoh.test')
            ->set('techStackIds', [$tech->id])
            ->set('specializationIds', [$specialization->id]);
    }

    public function test_a_php_file_disguised_as_an_image_is_rejected(): void
    {
        $this->actAsOwner();
        Storage::fake('private');

        // Ekstensi .jpg, tapi isinya kode PHP — hanya pemeriksaan isi berkas
        // yang bisa menangkap ini.
        $malicious = UploadedFile::fake()->createWithContent('shell.jpg', '<?php system($_GET["c"]); ?>');

        $this->formWithValidFields()
            ->set('image', $malicious)
            ->call('save')
            ->assertHasErrors('image');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_a_double_extension_filename_is_rejected(): void
    {
        $this->actAsOwner();
        Storage::fake('private');

        $file = UploadedFile::fake()->image('foto.php.jpg', 100, 100);

        $this->formWithValidFields()
            ->set('image', $file)
            ->call('save')
            ->assertHasErrors('image');
    }

    public function test_an_oversized_image_is_rejected(): void
    {
        $this->actAsOwner();
        Storage::fake('private');

        $file = UploadedFile::fake()->image('besar.jpg', 500, 500)->size(4096);

        $this->formWithValidFields()
            ->set('image', $file)
            ->call('save')
            ->assertHasErrors('image');
    }

    public function test_an_image_beyond_the_dimension_limit_is_rejected(): void
    {
        $this->actAsOwner();
        Storage::fake('private');

        $file = UploadedFile::fake()->image('raksasa.jpg', 5000, 5000);

        $this->formWithValidFields()
            ->set('image', $file)
            ->call('save')
            ->assertHasErrors('image');
    }

    public function test_a_valid_image_is_accepted_and_stored_with_a_random_name(): void
    {
        $this->actAsOwner();
        Storage::fake('private');

        $file = UploadedFile::fake()->image('asli.png', 600, 400);

        $this->formWithValidFields()
            ->set('image', $file)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('projects', 1);

        $stored = Projects::first()->image;

        // Nama asli tidak boleh dipertahankan; path harus acak di folder projects.
        $this->assertStringStartsWith('projects/', $stored);
        $this->assertStringNotContainsString('asli', $stored);
        Storage::disk('private')->assertExists($stored);
    }

    public function test_project_description_is_sanitized_before_it_is_saved(): void
    {
        $this->actAsOwner();
        Storage::fake('private');

        $this->formWithValidFields()
            ->set('description', '<p>Aman</p><script>alert(1)</script><a href="javascript:evil()">klik</a>')
            ->set('image', UploadedFile::fake()->image('ok.png', 300, 200))
            ->call('save')
            ->assertHasNoErrors();

        $description = Projects::first()->description;

        $this->assertStringNotContainsString('<script', $description);
        $this->assertStringNotContainsString('javascript:', $description);
        $this->assertStringContainsString('Aman', $description);
    }

    public function test_non_owner_cannot_create_a_project(): void
    {
        User::factory()->create();
        $intruder = User::factory()->create();
        $this->actingAs($intruder);

        Storage::fake('private');

        $this->formWithValidFields()
            ->set('image', UploadedFile::fake()->image('ok.png', 300, 200))
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseCount('projects', 0);
    }
}
