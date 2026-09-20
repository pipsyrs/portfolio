<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DisableTwoFactor extends Command
{
    protected $signature = 'user:disable-2fa {email? : Email pemilik; kosongkan untuk memakai akun pemilik}';

    protected $description = 'Mematikan 2FA lewat SSH ketika perangkat autentikator hilang';

    public function handle(): int
    {
        $user = $this->argument('email')
            ? User::where('email', $this->argument('email'))->first()
            : User::owner();

        if (! $user) {
            $this->error('Pengguna tidak ditemukan.');

            return self::FAILURE;
        }

        if (! $user->hasTwoFactorEnabled()) {
            $this->info('2FA memang tidak aktif untuk '.$user->email.'.');

            return self::SUCCESS;
        }

        $this->warn('Ini akan mematikan 2FA untuk: '.$user->email);

        if (! $this->confirm('Lanjutkan?', false)) {
            $this->line('Dibatalkan.');

            return self::SUCCESS;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        User::forgetOwnerCache();

        // Tindakan darurat ini harus meninggalkan jejak.
        Log::channel('security')->warning('Two-factor disabled via CLI', ['user' => $user->getKey()]);

        $this->info('2FA dimatikan. Aktifkan kembali dari menu Profil setelah masuk.');

        return self::SUCCESS;
    }
}
