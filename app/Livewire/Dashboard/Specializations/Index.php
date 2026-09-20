<?php

namespace App\Livewire\Dashboard\Specializations;

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Specializations;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
#[Title('Spesialisasi')]
class Index extends Component
{
    use InteractsWithToasts, WithPagination;

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    #[Locked]
    public ?string $editingId = null;

    #[Locked]
    public ?string $deletingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $icon = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'min:1', 'max:255',
                'unique:specializations,name'.($this->editingId ? ','.$this->editingId : ''),
            ],
            // Hanya kelas FontAwesome yang disimpan, bukan HTML sembarang.
            'icon' => ['required', 'string', 'max:80', 'regex:/^fa-(solid|regular|brands) fa-[a-z0-9-]+$/'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama spesialisasi wajib diisi.',
            'name.unique' => 'Spesialisasi dengan nama ini sudah ada.',
            'icon.required' => 'Ikon wajib dipilih.',
            'icon.regex' => 'Format ikon tidak valid.',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'icon']);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(string $id): void
    {
        $record = Specializations::findOrFail($id);

        $this->editingId = $record->id;
        $this->name = (string) $record->name;
        $this->icon = (string) $record->icon;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->reset(['editingId', 'name', 'icon', 'showForm']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('owner');

        $validated = $this->validate();

        if ($this->editingId) {
            Specializations::findOrFail($this->editingId)->update($validated);
            $this->toastSuccess('Spesialisasi berhasil diperbarui.');
        } else {
            Specializations::create($validated);
            $this->toastSuccess('Spesialisasi berhasil ditambahkan.');
        }

        $this->closeForm();
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function delete(): void
    {
        $this->authorize('owner');

        if ($this->deletingId === null) {
            return;
        }

        $record = Specializations::find($this->deletingId);

        if (! $record) {
            $this->deletingId = null;

            return;
        }

        $name = $record->name;
        $record->projects()->detach();
        $record->delete();

        $this->deletingId = null;
        $this->resetPage();
        $this->toastSuccess('Spesialisasi "'.$name.'" berhasil dihapus.');
    }

    public function render()
    {
        $items = Specializations::query()
            ->withCount('projects')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.dashboard.specializations.index', [
            'items' => $items,
        ]);
    }
}
