<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Dipakai bersama oleh halaman Backup dan perintah terjadwal.
 *
 * Kredensial database TIDAK pernah diletakkan di baris perintah — argumen
 * proses terlihat oleh seluruh pengguna server lewat `ps aux`. Kredensial
 * ditulis ke berkas sementara dengan izin 0600 dan dihapus setelah selesai.
 *
 * Hasil dump di-gzip lalu dienkripsi (lihat BackupCipher). Berkas lama
 * berekstensi .sql.gz tanpa enkripsi tetap dikenali agar bisa diunduh.
 */
class DatabaseBackupService
{
    public const EXTENSION = '.sql.gz.enc';

    public const LEGACY_EXTENSION = '.sql.gz';

    public function __construct(private readonly BackupCipher $cipher) {}

    public function directory(): string
    {
        $path = storage_path('app/backups');

        if (! File::exists($path)) {
            File::makeDirectory($path, 0750, true);
        }

        // Jaring pengaman bila direktori storage pernah salah dikonfigurasi
        // sehingga bisa dijangkau langsung lewat web server.
        $htaccess = $path.DIRECTORY_SEPARATOR.'.htaccess';

        if (! File::exists($htaccess)) {
            File::put($htaccess, "Require all denied\n");
        }

        return $path;
    }

    /**
     * @return array{name:string,size:string,bytes:int,date:string,encrypted:bool}[]
     */
    public function list(): array
    {
        return collect(File::files($this->directory()))
            ->filter(fn ($file) => $this->isBackupName($file->getFilename()))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => format_bytes($file->getSize()),
                'bytes' => $file->getSize(),
                'date' => date('d M Y, H:i', $file->getMTime()),
                'encrypted' => str_ends_with($file->getFilename(), self::EXTENSION),
            ])
            ->values()
            ->all();
    }

    public function isBackupName(string $name): bool
    {
        return (bool) preg_match(
            '/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql\.gz(\.enc)?$/',
            $name,
        );
    }

    /**
     * Mengembalikan jalur absolut berkas backup, atau null bila nama tidak sah
     * atau berkas berada di luar direktori backup.
     */
    public function path(string $filename): ?string
    {
        if (! $this->isBackupName($filename)) {
            return null;
        }

        $directory = realpath($this->directory());
        $path = realpath($directory.DIRECTORY_SEPARATOR.$filename);

        if ($path === false || ! str_starts_with($path, $directory.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $path;
    }

    public function create(): string
    {
        $config = $this->connection();

        $directory = $this->directory();
        $stamp = now()->format('Y-m-d_H-i-s');
        $archive = $directory.DIRECTORY_SEPARATOR.'backup_'.$stamp.self::LEGACY_EXTENSION;
        $target = $directory.DIRECTORY_SEPARATOR.'backup_'.$stamp.self::EXTENSION;

        $defaultsFile = $this->writeDefaultsFile($config);

        try {
            $this->dumpTo($archive, $defaultsFile, $config['database']);

            // Enkripsi berjalan setelah dump selesai, lalu arsip mentahnya
            // dihapus. Dump tidak pernah tersimpan permanen tanpa enkripsi.
            $this->cipher->encryptFile($archive, $target);

            if (! File::exists($target) || File::size($target) < 64) {
                throw new RuntimeException('Berkas terenkripsi gagal ditulis.');
            }

            $this->cipher->verify($target);

            @chmod($target, 0600);

            $this->prune($directory);

            return basename($target);
        } finally {
            foreach ([$archive, $defaultsFile] as $temp) {
                if (File::exists($temp)) {
                    File::delete($temp);
                }
            }
        }
    }

    /**
     * Mendekripsi backup ke berkas .sql.gz agar bisa dipulihkan secara manual.
     */
    public function decryptTo(string $filename, string $destination): string
    {
        $path = $this->path($filename);

        if ($path === null) {
            throw new RuntimeException("Berkas backup tidak ditemukan: {$filename}");
        }

        if (! str_ends_with($filename, self::EXTENSION)) {
            throw new RuntimeException('Berkas ini tidak terenkripsi, langsung bisa dipakai.');
        }

        $this->cipher->decryptFile($path, $destination);

        return $destination;
    }

    public function verify(string $filename): bool
    {
        $path = $this->path($filename);

        if ($path === null) {
            throw new RuntimeException("Berkas backup tidak ditemukan: {$filename}");
        }

        if (! str_ends_with($filename, self::EXTENSION)) {
            // Berkas lama tanpa enkripsi hanya bisa dicek keutuhan gzip-nya.
            $handle = @gzopen($path, 'rb');

            if ($handle === false) {
                throw new RuntimeException('Berkas gzip tidak dapat dibuka.');
            }

            $bytes = 0;
            $head = '';

            while (! gzeof($handle)) {
                $chunk = gzread($handle, 262144);

                if ($chunk === false) {
                    gzclose($handle);

                    throw new RuntimeException('Berkas gzip rusak.');
                }

                if ($head === '') {
                    $head = $chunk;
                }

                $bytes += strlen($chunk);
            }

            gzclose($handle);

            // gzip yang sah tapi isinya kosong adalah gejala khas dump yang
            // gagal di tengah jalan; itu bukan backup yang bisa dipulihkan.
            if ($bytes < 128 || ! str_contains($head, 'MySQL dump')) {
                throw new RuntimeException(
                    'Berkas gzip utuh tapi tidak berisi dump SQL yang sah ('.format_bytes($bytes).' setelah dibuka). '
                    .'Backup ini kemungkinan gagal saat dibuat dan tidak bisa dipulihkan.'
                );
            }

            return true;
        }

        return $this->cipher->verify($path);
    }

    public function delete(string $filename): bool
    {
        $path = $this->path($filename);

        return $path !== null && File::delete($path);
    }

    public function prune(?string $directory = null): int
    {
        $directory ??= $this->directory();
        $keep = max(1, (int) settings('backup_retention'));

        $stale = collect(File::files($directory))
            ->filter(fn ($file) => $this->isBackupName($file->getFilename()))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->slice($keep);

        $stale->each(fn ($file) => File::delete($file->getRealPath()));

        return $stale->count();
    }

    /**
     * Pemeriksaan pra-terbang. Dipakai halaman Backup dan perintah
     * `backup:doctor` supaya penyebab kegagalan terlihat sebelum dijalankan,
     * bukan muncul sebagai pesan "backup gagal" tanpa keterangan.
     *
     * @return array<string,array{ok:bool,label:string,detail:string}>
     */
    public function diagnostics(): array
    {
        $checks = [];

        $binary = $this->mysqldumpBinary();
        $resolved = $this->resolveBinary($binary);

        $checks['mysqldump'] = [
            'ok' => $resolved !== null,
            'label' => 'mysqldump',
            'detail' => $resolved
                ? $resolved.' · '.$this->binaryVersion($resolved)
                : 'Tidak ditemukan ("'.$binary.'"). Isi MYSQLDUMP_PATH di .env dengan jalur absolutnya.',
        ];

        try {
            $config = $this->connection();
            $checks['driver'] = [
                'ok' => true,
                'label' => 'Koneksi database',
                'detail' => $config['driver'].' · '.$config['database'].' @ '.$config['host'],
            ];
        } catch (\Throwable $e) {
            $checks['driver'] = ['ok' => false, 'label' => 'Koneksi database', 'detail' => $e->getMessage()];
        }

        $checks['key'] = [
            'ok' => $this->cipher->isConfigured(),
            'label' => 'Kunci enkripsi',
            'detail' => $this->cipher->isConfigured()
                ? 'BACKUP_ENCRYPTION_KEY terpasang'
                : 'Belum diisi. Jalankan: php artisan backup:key',
        ];

        $directory = $this->directory();
        $writable = is_writable($directory);
        $free = @disk_free_space($directory);

        $checks['storage'] = [
            'ok' => $writable && ($free === false || $free > 50 * 1024 * 1024),
            'label' => 'Ruang penyimpanan',
            'detail' => ! $writable
                ? 'Direktori tidak dapat ditulis: '.$directory
                : ($free === false ? $directory : format_bytes($free).' tersisa'),
        ];

        return $checks;
    }

    /**
     * @return array<string,mixed>
     */
    private function connection(): array
    {
        $name = config('database.default');
        $config = config('database.connections.'.$name);

        if (($config['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException(
                'Backup hanya mendukung koneksi MySQL/MariaDB, koneksi aktif: '
                .($config['driver'] ?? 'tidak diketahui').'.'
            );
        }

        return $config;
    }

    private function dumpTo(string $archive, string $defaultsFile, string $database): void
    {
        $binary = $this->resolveBinary($this->mysqldumpBinary());

        if ($binary === null) {
            throw new RuntimeException(
                'Perintah mysqldump tidak ditemukan. Isi MYSQLDUMP_PATH di .env dengan jalur absolutnya '
                .'(pada aaPanel biasanya /www/server/mysql/bin/mysqldump).'
            );
        }

        $process = new Process([
            $binary,
            '--defaults-extra-file='.$defaultsFile,
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--default-character-set=utf8mb4',
            $database,
        ], null, $this->processEnvironment());

        $process->setTimeout((float) config('backup.timeout', 600));

        $handle = gzopen($archive, 'wb6');

        if ($handle === false) {
            throw new RuntimeException('Tidak dapat menulis berkas sementara: '.$archive);
        }

        $stderr = '';

        try {
            $process->run(function (string $type, string $buffer) use ($handle, &$stderr) {
                if ($type === Process::OUT) {
                    gzwrite($handle, $buffer);

                    return;
                }

                $stderr .= $buffer;
            });
        } finally {
            gzclose($handle);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException($this->explainDumpFailure($stderr, $process->getExitCode()));
        }

        if (! File::exists($archive) || File::size($archive) < 32) {
            throw new RuntimeException('mysqldump selesai tanpa error tapi tidak menghasilkan data.');
        }
    }

    /**
     * Menerjemahkan keluaran mysqldump menjadi sebab yang bisa ditindaklanjuti.
     */
    private function explainDumpFailure(string $stderr, ?int $exitCode): string
    {
        $error = trim($stderr);

        return match (true) {
            str_contains($error, 'Access denied') => 'Akses database ditolak. Periksa DB_USERNAME dan DB_PASSWORD di .env, '
                .'dan pastikan user punya hak SELECT, LOCK TABLES, dan PROCESS.',

            str_contains($error, 'Can\'t connect') || str_contains($error, 'Unknown MySQL server host') => 'Tidak dapat terhubung ke server database. Periksa DB_HOST dan DB_PORT di .env.',

            str_contains($error, 'Unknown database') => 'Nama database tidak ditemukan. Periksa DB_DATABASE di .env.',

            str_contains($error, 'PROCESS privilege') => 'User database kekurangan hak PROCESS. Tambahkan hak tersebut lewat panel hosting.',

            str_contains($error, 'No space left') => 'Ruang disk server habis. Kosongkan ruang lalu ulangi.',

            str_contains($error, '10106') || str_contains($error, "Can't create TCP/IP socket") => 'mysqldump tidak bisa membuka soket jaringan. Lingkungan proses web server kehilangan '
                .'variabel SystemRoot; jalankan ulang layanan web setelah memastikan variabel itu tersedia.',

            $error !== '' => 'mysqldump gagal: '.Str::limit($error, 300),

            default => 'mysqldump berhenti dengan kode keluar '.($exitCode ?? '?').' tanpa pesan error.',
        };
    }

    private function writeDefaultsFile(array $config): string
    {
        $path = tempnam(sys_get_temp_dir(), 'dbdump');

        if ($path === false) {
            throw new RuntimeException('Tidak dapat membuat berkas kredensial sementara.');
        }

        // Dibatasi ke pemilik proses sebelum rahasia ditulis ke dalamnya.
        @chmod($path, 0600);

        File::put($path, "[client]\n"
            .'host='.$config['host']."\n"
            .'port='.($config['port'] ?? 3306)."\n"
            .'user='.$config['username']."\n"
            .'password="'.str_replace('"', '\"', (string) $config['password'])."\"\n");

        return $path;
    }

    private function mysqldumpBinary(): string
    {
        return (string) config('database.mysqldump_path', 'mysqldump');
    }

    /**
     * Variabel lingkungan tambahan untuk proses mysqldump.
     *
     * Di Windows, PHP-FPM/web server sering berjalan dengan lingkungan yang
     * sudah dipangkas. Tanpa SystemRoot, Winsock gagal inisialisasi dan
     * mysqldump berhenti dengan "Can't create TCP/IP socket (10106)" meski
     * binernya ketemu dan kredensialnya benar. Nilai di sini ditambahkan ke
     * lingkungan yang diwarisi, bukan menggantikannya.
     *
     * @return array<string,string>|null
     */
    private function processEnvironment(): ?array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return null;
        }

        $systemRoot = getenv('SystemRoot') ?: getenv('windir') ?: 'C:\\Windows';

        return [
            'SystemRoot' => $systemRoot,
            'SYSTEMROOT' => $systemRoot,
            'windir' => $systemRoot,
            'PATH' => implode(';', array_filter([
                getenv('PATH') ?: '',
                $systemRoot.'\\system32',
                $systemRoot,
                $systemRoot.'\\System32\\Wbem',
            ])),
        ];
    }

    /**
     * Mengembalikan jalur biner yang benar-benar ada, atau null.
     */
    private function resolveBinary(string $binary): ?string
    {
        if (str_contains($binary, '/') || str_contains($binary, DIRECTORY_SEPARATOR)) {
            return is_file($binary) ? $binary : null;
        }

        $found = (new ExecutableFinder)->find($binary);

        if ($found !== null) {
            return $found;
        }

        // PATH milik web server sering berbeda dari PATH shell — aaPanel dan
        // Laragon sama-sama menaruh biner di luar jangkauan proses PHP-FPM.
        foreach ($this->candidatePaths() as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function candidatePaths(): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [
                '/www/server/mysql/bin/mysqldump',
                '/usr/bin/mysqldump',
                '/usr/local/mysql/bin/mysqldump',
                '/opt/homebrew/bin/mysqldump',
            ];
        }

        $globs = [
            'C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\*\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\*\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB *\\bin\\mysqldump.exe',
        ];

        // Versi terbaru didahulukan supaya dump tidak dibuat klien yang lebih
        // tua dari server databasenya.
        return collect($globs)
            ->flatMap(fn (string $glob) => glob($glob) ?: [])
            ->sortDesc()
            ->values()
            ->all();
    }

    private function binaryVersion(string $binary): string
    {
        try {
            $process = new Process([$binary, '--version'], null, $this->processEnvironment());
            $process->setTimeout(10);
            $process->run();

            return $process->isSuccessful()
                ? trim(Str::before($process->getOutput(), "\n"))
                : 'versi tidak terbaca';
        } catch (\Throwable) {
            return 'versi tidak terbaca';
        }
    }
}
