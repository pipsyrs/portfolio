<?php

namespace Tests\Feature\Security;

use App\Services\BackupCipher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class BackupEncryptionTest extends TestCase
{
    use RefreshDatabase;

    private string $work;

    protected function setUp(): void
    {
        parent::setUp();

        config(['backup.encryption_key' => base64_encode(str_repeat('k', 32))]);

        $this->work = storage_path('framework/testing/backup-cipher');
        File::ensureDirectoryExists($this->work);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->work);

        parent::tearDown();
    }

    private function cipher(): BackupCipher
    {
        return new BackupCipher;
    }

    private function write(string $name, string $contents): string
    {
        $path = $this->work.DIRECTORY_SEPARATOR.$name;
        File::put($path, $contents);

        return $path;
    }

    public function test_it_round_trips_content_unchanged(): void
    {
        $plain = 'CREATE TABLE contoh (id INT);'.str_repeat("\nINSERT INTO contoh VALUES (1);", 500);

        $source = $this->write('dump.sql', $plain);
        $encrypted = $this->work.'/dump.sql.enc';
        $decrypted = $this->work.'/dump.out.sql';

        $this->cipher()->encryptFile($source, $encrypted);
        $this->cipher()->decryptFile($encrypted, $decrypted);

        $this->assertSame($plain, File::get($decrypted));
    }

    public function test_it_round_trips_payloads_larger_than_one_chunk(): void
    {
        // Lebih dari 1 MiB memaksa jalur multi-potongan, tempat penomoran IV
        // dan HMAC berjalan lintas potongan.
        $plain = random_bytes(3 * 1048576 + 1234);

        $source = $this->write('big.bin', $plain);
        $encrypted = $this->work.'/big.enc';
        $decrypted = $this->work.'/big.out';

        $this->cipher()->encryptFile($source, $encrypted);
        $this->cipher()->decryptFile($encrypted, $decrypted);

        $this->assertSame(md5($plain), md5_file($decrypted));
    }

    public function test_the_ciphertext_does_not_contain_the_plaintext(): void
    {
        $plain = 'RAHASIA-PENANDA-UNIK-12345';

        $encrypted = $this->work.'/secret.enc';
        $this->cipher()->encryptFile($this->write('secret.sql', $plain), $encrypted);

        $this->assertStringNotContainsString($plain, File::get($encrypted));
    }

    public function test_a_tampered_file_fails_verification(): void
    {
        $encrypted = $this->work.'/tamper.enc';
        $this->cipher()->encryptFile($this->write('tamper.sql', str_repeat('A', 4096)), $encrypted);

        $bytes = File::get($encrypted);
        // Ubah satu byte di tengah ciphertext.
        $bytes[100] = $bytes[100] === 'x' ? 'y' : 'x';
        File::put($encrypted, $bytes);

        $this->expectException(RuntimeException::class);
        $this->cipher()->verify($encrypted);
    }

    public function test_a_truncated_file_fails_verification(): void
    {
        $encrypted = $this->work.'/cut.enc';
        $this->cipher()->encryptFile($this->write('cut.sql', str_repeat('B', 8192)), $encrypted);

        File::put($encrypted, substr(File::get($encrypted), 0, -64));

        $this->expectException(RuntimeException::class);
        $this->cipher()->verify($encrypted);
    }

    public function test_a_wrong_key_fails_verification(): void
    {
        $encrypted = $this->work.'/wrongkey.enc';
        $this->cipher()->encryptFile($this->write('wrongkey.sql', 'data rahasia'), $encrypted);

        config(['backup.encryption_key' => base64_encode(str_repeat('z', 32))]);

        $this->expectException(RuntimeException::class);
        $this->cipher()->verify($encrypted);
    }

    public function test_a_missing_key_is_reported_clearly(): void
    {
        config(['backup.encryption_key' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/BACKUP_ENCRYPTION_KEY/');

        $this->cipher()->encryptFile($this->write('nokey.sql', 'x'), $this->work.'/nokey.enc');
    }

    public function test_a_foreign_file_is_rejected(): void
    {
        $foreign = $this->write('foreign.enc', str_repeat('Z', 512));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/bukan backup terenkripsi/');

        $this->cipher()->verify($foreign);
    }
}
