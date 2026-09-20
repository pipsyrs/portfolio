<?php

namespace App\Livewire\Sections;

use App\Models\Projects;
use App\Models\User;
use Livewire\Component;

class Hero extends Component
{
    public function render()
    {
        $user = User::first();

        return view('livewire.sections.hero', [
            'user' => $user,
            'projectsCount' => Projects::count(),
            'certificationsCount' => is_array($user?->certifications) ? count($user->certifications) : 0,
        ]);
    }
}
