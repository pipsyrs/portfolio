<?php

namespace Database\Seeders;

use App\Models\Specializations;
use Illuminate\Database\Seeder;

class SpecializationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Fullstack Developer',
            ],
            [
                'name' => 'Backend Developer',
            ],
            [
                'name' => 'Frontend Developer',
            ],
        ];

        foreach ($data as $item) {
            Specializations::firstOrCreate([
                'name' => $item['name'],
            ], $item);
        }
    }
}
