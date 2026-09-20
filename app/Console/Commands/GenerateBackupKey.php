<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateBackupKey extends Command
{
    protected $signature = 'backup:key {--show : Tampilkan kunci tanpa menulis ke .env}';

    protected $description = 'Membuat BACKUP_ENCRYPTION_KEY baru untuk enkripsi berkas backup';

    public function handle(): int
    {
        $key = base64_encode(random_bytes(32));

        if ($this->option('show')) {
            $this->line($key);

            return self::SUCCESS;
        }

        $path = base_path('.env');

        if (! File::exists($path)) {
            $this->error('Berkas .env tidak ditemukan.');

            return self::FAILURE;
        }

        $contents = File::get($path);

        if (str_contains($contents, 'BACKUP_ENCRYPTION_KEY=') && ! $this->confirmReplace($contents)) {
            return self::FAILURE;
        }

        $contents = str_contains($contents, 'BACKUP_ENCRYPTION_KEY=')
            ? preg_replace('/^BACKUP_ENCRYPTION_KEY=.*$/m', 'BACKUP_ENCRYPTION_KEY='.$key, $contents)
            : rtrim($contents)."\nBACKUP_ENCRYPTION_KEY=".$key."\n";

        File::put($path, $contents);

        $this->info('BACKUP_ENCRYPTION_KEY berhasil dibuat.');
        $this->newLine();
        $this->warn('Simpan salinan kunci ini di luar server.');
        $this->warn('Tanpa kunci ini, seluruh berkas backup tidak akan bisa dipulihkan.');

        return self::SUCCESS;
    }

    private function confirmReplace(string $contents): bool
    {
        // Kunci lama masih dibutuhkan untuk membuka backup yang sudah ada.
        preg_match('/^BACKUP_ENCRYPTION_KEY=(.*)$/m', $contents, $matches);

        if (blank($matches[1] ?? '')) {
            return true;
        }

        $this->warn('BACKUP_ENCRYPTION_KEY sudah terisi.');
        $this->warn('Menggantinya membuat SEMUA backup lama tidak bisa dipulihkan.');

        return $this->confirm('Tetap ganti kunci?', false);
    }
}
