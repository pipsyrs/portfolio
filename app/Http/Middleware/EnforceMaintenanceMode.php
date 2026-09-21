<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mode pemeliharaan versi aplikasi (sakelar di halaman Pengaturan), bukan
 * `php artisan down` — dashboard harus tetap bisa diakses supaya sakelarnya
 * bisa dimatikan lagi dari dalam.
 */
class EnforceMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! settings('maintenance_mode')) {
            return $next($request);
        }

        // Seluruh area dashboard dikecualikan, termasuk halaman login, supaya
        // pemilik tidak ikut terkunci di luar. Health check dilewatkan agar
        // pemantau uptime tidak menganggap server mati.
        if ($request->routeIs('dashboard.*') || $request->is('up')) {
            return $next($request);
        }

        // Request Livewire memakai routenya sendiri, sehingga pengecekan di atas
        // tidak mengenalinya. Tanpa ini tombol di halaman login ikut diblokir
        // dan pemilik terkunci di luar meski halamannya tampil.
        if ($this->isDashboardLivewireRequest()) {
            return $next($request);
        }

        // Pemilik yang sudah login tetap melihat situs apa adanya — termasuk
        // request Livewire dari landing page — sehingga hasil perubahan bisa
        // diperiksa sebelum mode ini dimatikan. Untuk pengunjung biasa request
        // Livewire ikut diblokir, jadi formulir kontak tidak bisa ditembak dari
        // halaman yang sudah terlanjur terbuka.
        if ($request->user()?->isOwner()) {
            return $next($request);
        }

        return response()
            ->view('errors.503', [], 503)
            ->header('Retry-After', '3600');
    }

    /**
     * Apakah request Livewire ini berasal dari halaman dashboard? Path asal
     * dibaca dari snapshot komponen, bukan dari Referer yang bisa dipalsukan.
     */
    private function isDashboardLivewireRequest(): bool
    {
        if (! Livewire::isLivewireRequest()) {
            return false;
        }

        try {
            $route = app('router')->getRoutes()->match(
                Request::create(Livewire::originalUrl())
            );
        } catch (HttpException) {
            // Path asal dikirim oleh klien, jadi bisa saja tidak cocok dengan
            // route mana pun. Perlakukan sebagai bukan dashboard.
            return false;
        }

        return str_starts_with((string) $route->getName(), 'dashboard.');
    }
}
