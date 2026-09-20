<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class VerifyBackup extends Command
{
    protected $signature = 'backup:verify {file? : Nama berkas; kosongkan untuk memeriksa semuanya}';

    protected $description = 'Memeriksa keaslian dan keutuhan berkas backup tanpa memulihkannya';

    public function handle(DatabaseBackupService $service): int
    {
        $files = $this->argument('file')
            ? [$this->argument('file')]
            : array_column($service->list(), 'name');

        if ($files === []) {
            $this->warn('Belum ada berkas backup.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($files as $file) {
            try {
                $service->verify($file);
                $this->line('  <fg=green>OK</>     '.$file);
            } catch (\Throwable $e) {
                $failed++;
                $this->line('  <fg=red>RUSAK</>  '.$file.' — '.$e->getMessage());
            }
        }

        $this->newLine();

        if ($failed > 0) {
            $this->error("{$failed} dari ".count($files).' berkas tidak lolos verifikasi.');

            return self::FAILURE;
        }

        $this->info(count($files).' berkas lolos verifikasi.');

        return self::SUCCESS;
    }
}
