<?php

namespace Database\Seeders;

use App\Models\TechStacks;
use Illuminate\Database\Seeder;

class TechStacksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Laravel',
            ],
            [
                'name' => 'NextJS',
            ],
            [
                'name' => 'Bootstrap',
            ],
            [
                'name' => 'TailwindCSS',
            ],
            [
                'name' => 'PHP',
            ],
            [
                'name' => 'JavaScript',
            ],
            [
                'name' => 'HTML',
            ],
            [
                'name' => 'CSS',
            ],
            [
                'name' => 'MySQL',
            ],
            [
                'name' => 'PostgreSQL',
            ],
        ];

        foreach ($data as $item) {
            TechStacks::firstOrCreate([
                'name' => $item['name'],
            ], $item);
        }
    }
}
