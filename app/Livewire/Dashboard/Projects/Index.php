<?php

namespace App\Livewire\Dashboard\Projects;

use App\Actions\Media\StoreSecureUpload;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Projects;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
#[Title('Projects')]
class Index extends Component
{
    use InteractsWithToasts, WithPagination;

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    /** Dikunci supaya nilainya tidak bisa diubah dari sisi klien. */
    #[Locked]
    public ?string $deletingId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function delete(StoreSecureUpload $uploads): void
    {
        $this->authorize('owner');

        if ($this->deletingId === null) {
            return;
        }

        $project = Projects::find($this->deletingId);

        if (! $project) {
            $this->deletingId = null;
            $this->toastError('Project sudah tidak ada.');

            return;
        }

        $name = $project->name;

        $project->techStacks()->detach();
        $project->specializations()->detach();
        $uploads->delete($project->image);
        $project->delete();

        $this->deletingId = null;
        $this->resetPage();

        $this->toastSuccess('Project "'.$name.'" berhasil dihapus.');
    }

    public function render()
    {
        $projects = Projects::query()
            ->with(['techStacks:id,name,icon', 'specializations:id,name,icon'])
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->latest('id')
            ->paginate(9);

        return view('livewire.dashboard.projects.index', [
            'projects' => $projects,
        ]);
    }
}
