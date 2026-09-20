<?php

namespace App\Livewire\Dashboard\Backup;

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\User;
use App\Notifications\BackupCompleted;
use App\Notifications\BackupFailed;
use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.dashboard')]
#[Title('Backup Database')]
class Index extends Component
{
    use InteractsWithToasts;

    #[Locked]
    public ?string $deletingFile = null;

    public bool $running = false;

    public bool $confirmingBackup = false;

    private const HOURLY_LIMIT = 3;

    public function confirmBackup(): void
    {
        $this->confirmingBackup = true;
    }

    public function cancelBackup(): void
    {
        $this->confirmingBackup = false;
    }

    public function backup(DatabaseBackupService $service): void
    {
        $this->authorize('owner');

        $this->confirmingBackup = false;

        $key = 'db-backup:'.auth()->id();

        // mysqldump berat untuk VPS kecil — batasi agar tidak bisa dipicu
        // berulang kali sampai server kehabisan sumber daya.
        if (RateLimiter::tooManyAttempts($key, self::HOURLY_LIMIT)) {
            $minutes = ceil(RateLimiter::availableIn($key) / 60);

            $this->toastError("Batas backup manual tercapai. Coba lagi dalam {$minutes} menit.");

            return;
        }

        if (! $this->ready) {
            $this->toastError('Perbaiki pemeriksaan yang gagal di panel atas lebih dulu.', 'Backup belum siap');

            return;
        }

        RateLimiter::hit($key, 3600);
        $this->running = true;

        try {
            $filename = $service->create();
            $size = collect($service->list())->firstWhere('name', $filename)['size'] ?? '-';

            User::owner()?->notify(new BackupCompleted($filename, $size));

            unset($this->diagnostics, $this->ready);

            $this->toastSuccess('Backup berhasil dibuat: '.$filename);
        } catch (\Throwable $e) {
            Log::error('Manual database backup failed', ['error' => $e->getMessage()]);

            User::owner()?->notify(new BackupFailed($e->getMessage()));

            $this->toastError($e->getMessage(), 'Backup gagal');
        } finally {
            $this->running = false;
        }
    }

    /**
     * Pemeriksaan pra-terbang ditampilkan di halaman, sehingga penyebab
     * kegagalan terlihat sebelum tombol ditekan.
     *
     * @return array<string,array{ok:bool,label:string,detail:string}>
     */
    #[Computed]
    public function diagnostics(): array
    {
        return app(DatabaseBackupService::class)->diagnostics();
    }

    #[Computed]
    public function ready(): bool
    {
        return collect($this->diagnostics())->every(fn ($check) => $check['ok']);
    }

    public function verifyFile(string $file, DatabaseBackupService $service): void
    {
        $this->authorize('owner');

        try {
            $service->verify($file);
            $this->toastSuccess('Berkas utuh dan dapat dipulihkan.', $file);
        } catch (\Throwable $e) {
            $this->toastError($e->getMessage(), 'Verifikasi gagal');
        }
    }

    public function confirmDelete(string $file): void
    {
        $this->deletingFile = $file;
    }

    public function cancelDelete(): void
    {
        $this->deletingFile = null;
    }

    public function delete(DatabaseBackupService $service): void
    {
        $this->authorize('owner');

        if ($this->deletingFile === null) {
            return;
        }

        $file = $this->deletingFile;
        $this->deletingFile = null;

        if ($service->delete($file)) {
            $this->toastSuccess('Backup '.$file.' berhasil dihapus.');

            return;
        }

        $this->toastError('Berkas backup tidak ditemukan.');
    }

    /**
     * Tautan unduh bertanda tangan dan hanya berlaku 5 menit, sehingga URL yang
     * bocor dari riwayat browser atau log proxy tidak bisa dipakai ulang.
     */
    public function downloadUrl(string $file): string
    {
        return URL::temporarySignedRoute('dashboard.backup.download', now()->addMinutes(5), ['file' => $file]);
    }

    public function render(DatabaseBackupService $service)
    {
        $backups = $service->list();

        return view('livewire.dashboard.backup.index', [
            'backups' => $backups,
            'totalSize' => format_bytes(array_sum(array_column($backups, 'bytes'))),
        ]);
    }
}
