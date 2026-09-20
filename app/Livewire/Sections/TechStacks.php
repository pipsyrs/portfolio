<?php

namespace App\Livewire\Sections;

use App\Models\TechStacks as ModelsTechStacks;
use Livewire\Component;

class TechStacks extends Component
{
    public function render()
    {
        return view('livewire.sections.tech-stacks', [
            'techStacks' => ModelsTechStacks::all(),
        ]);
    }
}
