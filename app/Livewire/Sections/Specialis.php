<?php

namespace App\Livewire\Sections;

use App\Models\Specializations;
use Livewire\Component;

class Specialis extends Component
{
    public function render()
    {
        return view('livewire.sections.specialis', [
            'specializations' => Specializations::all(),
        ]);
    }
}
