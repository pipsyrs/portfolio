<?php

namespace App\Livewire\Dashboard\Profile;

use App\Livewire\Concerns\InteractsWithToasts;
use App\Services\SessionRegistry;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Sessions extends Component
{
    use InteractsWithToasts;

    #[Locked]
    public ?string $revokingId = null;

    public bool $askingPassword = false;

    public string $password = '';

    #[Computed]
    public function sessions(): array
    {
        return app(SessionRegistry::class)->forUser(auth()->id(), session()->getId());
    }

    #[Computed]
    public function supported(): bool
    {
        return app(SessionRegistry::class)->isSupported();
    }

    public function confirmRevoke(string $id): void
    {
        // Sesi sendiri diakhiri lewat tombol Keluar, bukan dari daftar ini.
        if ($id === session()->getId()) {
            $this->toastInfo('Gunakan tombol Keluar untuk mengakhiri sesi perangkat ini.');

            return;
        }

        $this->revokingId = $id;
    }

    public function cancelRevoke(): void
    {
        $this->revokingId = null;
    }

    public function revoke(SessionRegistry $registry): void
    {
        $this->authorize('owner');

        if ($this->revokingId === null) {
            return;
        }

        $revoked = $registry->revoke(auth()->id(), $this->revokingId);
        $this->revokingId = null;

        unset($this->sessions);

        $revoked
            ? $this->toastSuccess('Sesi perangkat tersebut sudah diakhiri.')
            : $this->toastError('Sesi tidak ditemukan, mungkin sudah berakhir.');
    }

    public function askPassword(): void
    {
        $this->reset('password');
        $this->resetValidation();
        $this->askingPassword = true;
    }

    public function cancelPassword(): void
    {
        $this->reset(['password', 'askingPassword']);
        $this->resetValidation();
    }

    /**
     * Mencabut seluruh sesi lain. Memerlukan kata sandi karena ini tindakan
     * pemulihan setelah dugaan pembajakan — dan sesi yang dibajak tidak
     * boleh bisa memakainya untuk mengunci pemilik aslinya.
     */
    public function revokeOthers(SessionRegistry $registry): void
    {
        $this->authorize('owner');

        $this->validate(
            ['password' => ['required', 'string']],
            ['password.required' => 'Masukkan kata sandi untuk melanjutkan.'],
        );

        if (! Hash::check($this->password, auth()->user()->password)) {
            $this->addError('password', 'Kata sandi tidak sesuai.');

            return;
        }

        $count = $registry->revokeOthers(auth()->id(), session()->getId());

        $this->cancelPassword();
        unset($this->sessions);

        $count > 0
            ? $this->toastSuccess($count.' sesi lain berhasil diakhiri.')
            : $this->toastInfo('Tidak ada sesi lain yang aktif.');
    }

    public function render()
    {
        return view('livewire.dashboard.profile.sessions');
    }
}
