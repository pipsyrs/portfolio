<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\BackupCompleted;
use App\Notifications\BackupFailed;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Backup database ke berkas .sql.gz';

    public function handle(DatabaseBackupService $service): int
    {
        $this->info('Memulai backup database...');

        try {
            $filename = $service->create();
            $size = collect($service->list())->firstWhere('name', $filename)['size'] ?? '-';

            User::owner()?->notify(new BackupCompleted($filename, $size, automatic: true));

            $this->info('Backup berhasil: '.$filename.' ('.$size.')');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Scheduled database backup failed', ['error' => $e->getMessage()]);

            User::owner()?->notify(new BackupFailed($e->getMessage(), automatic: true));

            $this->error('Backup gagal: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
