<?php

namespace Database\Seeders;

use App\Models\Projects;
use App\Models\Specializations;
use App\Models\TechStacks;
use Illuminate\Database\Seeder;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Project 1',
                'description' => 'Description for Project 1',
                'image' => 'projects/1.jpg',
                'url' => 'https://google.com',
            ],
            [
                'name' => 'Project 2',
                'description' => 'Description for Project 2',
                'image' => 'projects/2.jpg',
                'url' => 'https://google.com',
            ],
        ];

        // Fetch all IDs once to avoid redundant queries
        $techStackIds = TechStacks::pluck('id');
        $specializationIds = Specializations::pluck('id');

        foreach ($projects as $projectData) {
            $project = Projects::firstOrCreate(
                ['name' => $projectData['name']],
                $projectData
            );

            // Attach random tech stacks (if any exist)
            if ($techStackIds->isNotEmpty()) {
                $project->techStacks()->attach(
                    $techStackIds->random(rand(1, min(3, $techStackIds->count())))
                );
            }

            // Attach random specializations (if any exist)
            if ($specializationIds->isNotEmpty()) {
                $project->specializations()->attach(
                    $specializationIds->random(rand(1, min(3, $specializationIds->count())))
                );
            }
        }
    }
}
