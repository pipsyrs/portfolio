<?php

namespace App\Livewire\Dashboard;

use App\Models\Contacts;
use App\Models\Projects;
use App\Models\Specializations;
use App\Models\TechStacks;
use App\Models\User;
use App\Models\Visitor;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.dashboard')]
#[Title('Dashboard')]
class Home extends Component
{
    #[Computed]
    public function stats(): array
    {
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();

        return [
            'visitors_today' => Visitor::where('visited_date', $today)->count(),
            'visitors_week' => Visitor::where('visited_date', '>=', $weekStart)->count(),
            'visitors_total' => Visitor::count(),
            'projects' => Projects::count(),
            'tech_stacks' => TechStacks::count(),
            'specializations' => Specializations::count(),
            'contacts' => Contacts::count(),
            'contacts_week' => Contacts::where('created_at', '>=', now()->startOfWeek())->count(),
        ];
    }

    #[Computed]
    public function recentContacts()
    {
        return Contacts::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'subject', 'created_at']);
    }

    #[Computed]
    public function topCountries()
    {
        return Visitor::query()
            ->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as total')
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.home', [
            'owner' => User::owner(),
        ]);
    }
}
