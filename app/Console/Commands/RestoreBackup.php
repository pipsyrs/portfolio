<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreBackup extends Command
{
    protected $signature = 'backup:restore
                            {file : Nama berkas backup}
                            {--decrypt-only= : Hanya dekripsi ke jalur ini, tanpa memulihkan}';

    protected $description = 'Memulihkan database dari berkas backup terenkripsi';

    public function handle(DatabaseBackupService $service): int
    {
        $file = $this->argument('file');

        try {
            $service->verify($file);
        } catch (\Throwable $e) {
            $this->error('Verifikasi gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Berkas lolos verifikasi.');

        $target = $this->option('decrypt-only');

        if ($target) {
            $service->decryptTo($file, $target);
            $this->info('Berkas didekripsi ke: '.$target);

            return self::SUCCESS;
        }

        $database = config('database.connections.'.config('database.default').'.database');

        $this->newLine();
        $this->warn('Pemulihan akan MENIMPA seluruh isi database "'.$database.'".');
        $this->warn('Buat backup terbaru lebih dulu bila data saat ini masih dibutuhkan.');

        if (! $this->confirm('Lanjutkan pemulihan?', false)) {
            $this->line('Dibatalkan.');

            return self::SUCCESS;
        }

        $temp = storage_path('app/backups/.restore-'.uniqid().'.sql.gz');

        try {
            $service->decryptTo($file, $temp);

            $this->line('Berkas siap dipulihkan: '.$temp);
            $this->newLine();
            $this->line('Jalankan perintah berikut untuk memulihkan:');
            $this->newLine();
            $this->line('  gunzip < '.$temp.' | mysql -u <user> -p '.$database);
            $this->newLine();
            $this->warn('Hapus berkas sementara itu setelah selesai.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            if (File::exists($temp)) {
                File::delete($temp);
            }

            $this->error('Gagal: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
