<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Aplikasi ini single-account: satu baris user adalah pemilik portfolio.
        // Kredensial awal dibaca dari .env supaya tidak ada kata sandi tetap di
        // dalam repositori; ganti segera setelah login pertama.
        $email = env('OWNER_EMAIL', 'owner@example.com');
        $password = env('OWNER_PASSWORD', '12345678');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => env('OWNER_NAME', 'Portfolio Owner'),
                'phone' => '0000000000',
                'password' => Hash::make($password),
            ],
        );

        $this->command?->warn('User pemilik dibuat dengan email: '.$email);
        $this->command?->warn('Segera ganti kata sandi lewat menu Profil setelah login pertama.');
    }
}
