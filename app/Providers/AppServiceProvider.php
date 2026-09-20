<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->guardBroadcaster();
    }

    /**
     * Turunkan broadcaster ke driver null bila kuncinya belum diisi.
     *
     * routes/channels.php me-resolve broadcaster saat boot, sehingga
     * BROADCAST_CONNECTION=reverb tanpa kredensial membuat seluruh aplikasi
     * gagal start — bukan cuma notifikasinya. Dengan penjagaan ini, Reverb
     * yang belum siap cukup membuat dashboard kembali memakai polling.
     */
    private function guardBroadcaster(): void
    {
        $connection = config('broadcasting.default');

        if ($connection === null || $connection === 'null' || $connection === 'log') {
            return;
        }

        if (blank(config('broadcasting.connections.'.$connection.'.key'))) {
            config(['broadcasting.default' => 'null']);
        }
    }

    public function boot(): void
    {
        // Aplikasi single-account: hanya pemilik yang boleh menyentuh dashboard.
        Gate::define('owner', fn (User $user) => $user->isOwner());

        // Paksa HTTPS di production supaya cookie sesi & signed URL tidak pernah
        // dikirim lewat koneksi polos.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Request::macro('realIp', fn () => get_real_ip());
        Request::macro('ipLocation', fn () => get_location_from_ip($this->realIp()));

        // Dipakai di layout untuk menandai item navigasi aktif.
        Blade::if('activeRoute', fn (string ...$patterns) => request()->routeIs(...$patterns));
    }
}
