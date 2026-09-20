<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;

/**
 * Rotasi APP_KEY membuat semua nilai terenkripsi tidak terbaca. Perintah ini
 * mendekripsi dengan kunci lama lalu menulis ulang dengan kunci yang berlaku
 * sekarang, sehingga rotasi tidak menghanguskan kredensial dan secret 2FA.
 *
 * Urutan pemakaian:
 *   1. php artisan settings:rekey --dump   (simpan nilai lama, kunci LAMA masih aktif)
 *   2. ganti APP_KEY di .env
 *   3. php artisan settings:rekey --old-key=base64:...
 */
class RekeySettings extends Command
{
    protected $signature = 'settings:rekey
                            {--old-key= : APP_KEY lama (base64:...)}
                            {--dump : Hanya tampilkan nilai terenkripsi yang akan terpengaruh}';

    protected $description = 'Menulis ulang nilai terenkripsi setelah APP_KEY dirotasi';

    /** Kolom terenkripsi di tabel biasa. */
    private const ENCRYPTED_COLUMNS = [
        'users' => ['two_factor_secret', 'two_factor_recovery_codes'],
    ];

    public function handle(): int
    {
        if ($this->option('dump')) {
            return $this->dump();
        }

        $oldKey = (string) $this->option('old-key');

        if ($oldKey === '') {
            $this->error('Sertakan --old-key=base64:... (APP_KEY sebelum rotasi).');
            $this->line('Jalankan lebih dulu: php artisan settings:rekey --dump');

            return self::FAILURE;
        }

        try {
            $old = new Encrypter($this->parseKey($oldKey), config('app.cipher'));
        } catch (\Throwable $e) {
            $this->error('Kunci lama tidak valid: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->warn('Nilai terenkripsi akan ditulis ulang dengan APP_KEY yang berlaku sekarang.');
        $this->warn('Pastikan Anda sudah punya backup database terbaru.');

        if (! $this->confirm('Lanjutkan?', false)) {
            $this->line('Dibatalkan.');

            return self::SUCCESS;
        }

        $rewritten = 0;
        $skipped = 0;

        foreach ($this->targets() as [$table, $id, $column, $value, $label]) {
            if ($this->readableWithCurrentKey($value)) {
                $skipped++;

                continue;
            }

            try {
                $fresh = encrypt($old->decrypt($value, false), false);
            } catch (\Throwable) {
                $this->warn("  Lewat: {$label} tidak terbaca kunci lama.");
                $skipped++;

                continue;
            }

            DB::table($table)->where('id', $id)->update([$column => $fresh]);
            $rewritten++;
        }

        settings()->flush();
        User::forgetOwnerCache();

        $this->newLine();
        $this->info("{$rewritten} nilai ditulis ulang, {$skipped} dilewati.");
        $this->line('Uji ulang koneksi SMTP dan login 2FA sebelum menganggap selesai.');

        return self::SUCCESS;
    }

    private function dump(): int
    {
        $rows = [];

        foreach ($this->targets() as [, , , $value, $label]) {
            $rows[] = [$label, $this->readableWithCurrentKey($value) ? 'terbaca' : 'TIDAK TERBACA'];
        }

        if ($rows === []) {
            $this->info('Tidak ada nilai terenkripsi yang terisi.');

            return self::SUCCESS;
        }

        $this->table(['Nilai', 'Status kunci saat ini'], $rows);
        $this->newLine();
        $this->line('Catat APP_KEY yang berlaku sekarang sebelum menggantinya.');

        return self::SUCCESS;
    }

    /**
     * Setiap nilai terenkripsi di basis data, tanpa peduli bentuk tabelnya.
     *
     * @return \Generator<array{0:string,1:string,2:string,3:string,4:string}>
     */
    private function targets(): \Generator
    {
        foreach (self::ENCRYPTED_COLUMNS as $table => $columns) {
            foreach (DB::table($table)->get() as $row) {
                foreach ($columns as $column) {
                    if (blank($row->{$column} ?? null)) {
                        continue;
                    }

                    yield [$table, $row->id, $column, $row->{$column}, "{$table}.{$column} #{$row->id}"];
                }
            }
        }

        // Pengaturan berbentuk key/value: yang terenkripsi adalah kolom `value`
        // pada baris yang key-nya terdaftar sebagai rahasia.
        $secrets = settings()->secretKeys();

        if ($secrets === []) {
            return;
        }

        foreach (DB::table('settings')->whereIn('key', $secrets)->get() as $row) {
            if (blank($row->value)) {
                continue;
            }

            yield ['settings', $row->id, 'value', $row->value, "settings.{$row->key}"];
        }
    }

    private function readableWithCurrentKey(string $value): bool
    {
        try {
            decrypt($value, false);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function parseKey(string $key): string
    {
        return str_starts_with($key, 'base64:')
            ? base64_decode(substr($key, 7), true) ?: ''
            : $key;
    }
}
