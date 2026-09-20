<?php

namespace App\Livewire\Dashboard\Projects;

use App\Actions\Media\SanitizeRichText;
use App\Actions\Media\StoreSecureUpload;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Projects;
use App\Models\Specializations;
use App\Models\TechStacks;
use App\Rules\SafeImageUpload;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.dashboard')]
#[Title('Form Project')]
class Form extends Component
{
    use InteractsWithToasts, WithFileUploads;

    #[Locked]
    public ?string $projectId = null;

    #[Locked]
    public ?string $currentImage = null;

    public string $name = '';

    public string $description = '';

    public string $url = '';

    public string $github_link = '';

    /** @var array<int,string> */
    public array $techStackIds = [];

    /** @var array<int,string> */
    public array $specializationIds = [];

    public ?TemporaryUploadedFile $image = null;

    public function mount(?Projects $project = null): void
    {
        if (! $project?->exists) {
            return;
        }

        $this->projectId = $project->id;
        $this->currentImage = $project->image;
        $this->name = (string) $project->name;
        $this->description = (string) $project->description;
        $this->url = (string) $project->url;
        $this->github_link = (string) $project->github_link;
        $this->techStackIds = $project->techStacks()->pluck('tech_stacks.id')->all();
        $this->specializationIds = $project->specializations()->pluck('specializations.id')->all();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:20000'],
            'url' => ['required', 'string', 'max:255', 'url:http,https'],
            'github_link' => ['nullable', 'string', 'max:255', 'url:http,https'],
            // Gambar wajib saat membuat baru; saat mengedit boleh dibiarkan.
            'image' => [$this->projectId ? 'nullable' : 'required', new SafeImageUpload(2048)],
            'techStackIds' => ['required', 'array', 'min:1'],
            'techStackIds.*' => ['uuid', 'exists:tech_stacks,id'],
            'specializationIds' => ['required', 'array', 'min:1'],
            'specializationIds.*' => ['uuid', 'exists:specializations,id'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama project wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'description.min' => 'Deskripsi minimal 10 karakter.',
            'url.required' => 'URL project wajib diisi.',
            'url.url' => 'URL harus diawali http:// atau https://.',
            'github_link.url' => 'Link GitHub harus berupa URL yang valid.',
            'image.required' => 'Gambar project wajib diunggah.',
            'techStackIds.required' => 'Pilih minimal satu tech stack.',
            'specializationIds.required' => 'Pilih minimal satu spesialisasi.',
        ];
    }

    public function save(StoreSecureUpload $uploads, SanitizeRichText $sanitize): void
    {
        $this->authorize('owner');

        $validated = $this->validate();

        $payload = [
            'name' => $validated['name'],
            'description' => $sanitize($validated['description']),
            'url' => $validated['url'],
            'github_link' => $validated['github_link'] ?: null,
        ];

        if ($this->image instanceof TemporaryUploadedFile) {
            $payload['image'] = $uploads->image($this->image, 'projects');
        }

        $project = $this->projectId
            ? Projects::findOrFail($this->projectId)
            : new Projects;

        $oldImage = $project->image;

        $project->fill($payload)->save();

        // Berkas lama baru dihapus setelah yang baru tersimpan, supaya
        // kegagalan di tengah jalan tidak meninggalkan project tanpa gambar.
        if (isset($payload['image']) && $oldImage && $oldImage !== $payload['image']) {
            $uploads->delete($oldImage);
        }

        $project->techStacks()->sync($validated['techStackIds']);
        $project->specializations()->sync($validated['specializationIds']);

        $this->toastSuccess($this->projectId ? 'Project berhasil diperbarui.' : 'Project berhasil dibuat.');

        $this->redirectRoute('dashboard.projects', navigate: true);
    }

    public function render()
    {
        return view('livewire.dashboard.projects.form', [
            'techStacks' => TechStacks::orderBy('name')->get(['id', 'name', 'icon']),
            'specializations' => Specializations::orderBy('name')->get(['id', 'name', 'icon']),
        ]);
    }
}
