<?php

namespace App\Livewire\Dashboard\Profile;

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TwoFactor extends Component
{
    use InteractsWithToasts;

    /** Secret kandidat selama pemasangan; baru disimpan setelah kode terbukti. */
    #[Locked]
    public ?string $pendingSecret = null;

    /** @var array<int,string> */
    #[Locked]
    public array $pendingRecoveryCodes = [];

    public string $code = '';

    public string $password = '';

    public bool $disabling = false;

    public bool $showRecoveryCodes = false;

    public function startSetup(TwoFactorService $service): void
    {
        $this->authorize('owner');

        $this->pendingSecret = $service->generateSecret();
        $this->pendingRecoveryCodes = $service->generateRecoveryCodes();
        $this->reset(['code']);
        $this->resetValidation();
    }

    public function cancelSetup(): void
    {
        $this->reset(['pendingSecret', 'pendingRecoveryCodes', 'code']);
        $this->resetValidation();
    }

    public function confirm(TwoFactorService $service): void
    {
        $this->authorize('owner');

        if ($this->pendingSecret === null) {
            return;
        }

        $this->validate(
            ['code' => ['required', 'string', 'size:6']],
            [
                'code.required' => 'Masukkan kode 6 digit dari aplikasi autentikator.',
                'code.size' => 'Kode harus 6 digit.',
            ],
        );

        if (! $service->verify($this->pendingSecret, $this->code)) {
            $this->addError('code', 'Kode tidak cocok. Pastikan jam perangkat Anda tepat, lalu coba lagi.');

            return;
        }

        $user = $this->user();
        $user->forceFill([
            'two_factor_secret' => $this->pendingSecret,
            'two_factor_recovery_codes' => $this->pendingRecoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        Log::channel('security')->info('Two-factor enabled', ['user' => $user->id, 'ip' => get_real_ip()]);

        $this->reset(['pendingSecret', 'code']);
        $this->showRecoveryCodes = true;

        User::forgetOwnerCache();

        $this->toastSuccess('Autentikasi dua langkah aktif. Simpan kode pemulihan Anda.');
    }

    public function regenerateRecoveryCodes(TwoFactorService $service): void
    {
        $this->authorize('owner');

        if (! $this->user()->hasTwoFactorEnabled()) {
            return;
        }

        $codes = $service->generateRecoveryCodes();

        $this->user()->forceFill(['two_factor_recovery_codes' => $codes])->save();

        $this->pendingRecoveryCodes = $codes;
        $this->showRecoveryCodes = true;

        $this->toastSuccess('Kode pemulihan baru dibuat. Kode lama tidak berlaku lagi.');
    }

    public function askDisable(): void
    {
        $this->reset(['password']);
        $this->resetValidation();
        $this->disabling = true;
    }

    public function cancelDisable(): void
    {
        $this->reset(['password', 'disabling']);
        $this->resetValidation();
    }

    public function disable(): void
    {
        $this->authorize('owner');

        $this->validate(
            ['password' => ['required', 'string']],
            ['password.required' => 'Masukkan kata sandi untuk melanjutkan.'],
        );

        // Mematikan 2FA menurunkan tingkat keamanan akun, jadi harus dibuktikan
        // dengan kata sandi — bukan cukup memegang sesi yang sudah terbuka.
        if (! Hash::check($this->password, $this->user()->password)) {
            $this->addError('password', 'Kata sandi tidak sesuai.');

            return;
        }

        $this->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        Log::channel('security')->warning('Two-factor disabled', ['user' => $this->user()->id, 'ip' => get_real_ip()]);

        User::forgetOwnerCache();

        $this->cancelDisable();
        $this->reset(['pendingRecoveryCodes', 'showRecoveryCodes']);

        $this->toastWarning('Autentikasi dua langkah dimatikan.');
    }

    public function hideRecoveryCodes(): void
    {
        $this->reset(['showRecoveryCodes', 'pendingRecoveryCodes']);
    }

    private function user(): User
    {
        return auth()->user();
    }

    public function render(TwoFactorService $service)
    {
        return view('livewire.dashboard.profile.two-factor', [
            'enabled' => $this->user()->hasTwoFactorEnabled(),
            'qr' => $this->pendingSecret ? $service->qrCodeSvg($this->user(), $this->pendingSecret) : null,
            'remainingCodes' => count($this->user()->recoveryCodes()),
        ]);
    }
}
