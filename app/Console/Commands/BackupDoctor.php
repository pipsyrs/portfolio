<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDoctor extends Command
{
    protected $signature = 'backup:doctor';

    protected $description = 'Memeriksa kesiapan backup database dan melaporkan penyebab kegagalan';

    public function handle(DatabaseBackupService $service): int
    {
        $this->newLine();
        $this->line('Pemeriksaan kesiapan backup');
        $this->newLine();

        $failed = 0;

        foreach ($service->diagnostics() as $check) {
            if ($check['ok']) {
                $this->line('  <fg=green>OK</>     '.$check['label'].': '.$check['detail']);

                continue;
            }

            $failed++;
            $this->line('  <fg=red>GAGAL</>  '.$check['label'].': '.$check['detail']);
        }

        $this->newLine();

        if ($failed > 0) {
            $this->error("{$failed} pemeriksaan gagal. Perbaiki lebih dulu sebelum menjalankan backup.");

            return self::FAILURE;
        }

        $this->info('Semua pemeriksaan lolos. Backup siap dijalankan.');

        return self::SUCCESS;
    }
}
