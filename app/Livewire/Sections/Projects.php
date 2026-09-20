<?php

namespace App\Livewire\Sections;

use App\Models\Projects as ProjectModel;
use Livewire\Component;

class Projects extends Component
{
    public function render()
    {
        return view('livewire.sections.projects', [
            'projects' => ProjectModel::with('techStacks')->orderBy('created_at', 'desc')->get(),
        ]);
    }
}
