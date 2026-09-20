<?php

namespace App\Livewire\Dashboard\Visitors;

use App\Models\Visitor;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Table extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $visitors = Visitor::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(fn ($q) => $q
                    ->where('ip_address', 'like', $term)
                    ->orWhere('country', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('user_agent', 'like', $term));
            })
            ->latest('visited_date')
            ->latest('id')
            ->paginate(10);

        return view('livewire.dashboard.visitors.table', [
            'visitors' => $visitors,
        ]);
    }
}
