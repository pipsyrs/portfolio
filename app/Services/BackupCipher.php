<?php

namespace App\Services;

use RuntimeException;

/**
 * Enkripsi berkas backup dengan AES-256-CTR + HMAC-SHA256 (encrypt-then-MAC).
 *
 * Diproses per potongan 1 MiB, bukan sekaligus, supaya dump berukuran puluhan
 * megabyte tetap muat di VPS dengan RAM 1 GB.
 *
 * Setiap potongan memakai IV unik (nonce acak + nomor urut potongan) sehingga
 * keystream tidak pernah dipakai ulang, dan HMAC dihitung atas seluruh aliran
 * sehingga potongan tidak bisa ditukar, diulang, atau dipotong tanpa ketahuan.
 *
 * Tata letak berkas:
 *   "PFBK1\0"  6 byte   penanda format
 *   nonce      8 byte   acak per berkas
 *   ciphertext n byte   potongan-potongan berurutan
 *   hmac      32 byte   atas penanda + nonce + seluruh ciphertext
 */
class BackupCipher
{
    private const MAGIC = "PFBK1\0";

    private const CHUNK = 1048576; // 1 MiB

    private const HMAC_BYTES = 32;

    public function isConfigured(): bool
    {
        return filled(config('backup.encryption_key'));
    }

    public function encryptFile(string $source, string $destination): void
    {
        [$encKey, $macKey] = $this->keys();

        $in = $this->open($source, 'rb');
        $out = $this->open($destination, 'wb');

        try {
            $nonce = random_bytes(8);
            $header = self::MAGIC.$nonce;

            fwrite($out, $header);

            $hmac = hash_init('sha256', HASH_HMAC, $macKey);
            hash_update($hmac, $header);

            $counter = 0;

            while (! feof($in)) {
                $plain = fread($in, self::CHUNK);

                if ($plain === false || $plain === '') {
                    continue;
                }

                $cipher = openssl_encrypt(
                    $plain,
                    'aes-256-ctr',
                    $encKey,
                    OPENSSL_RAW_DATA,
                    $this->iv($nonce, $counter),
                );

                if ($cipher === false) {
                    throw new RuntimeException('Enkripsi potongan backup gagal.');
                }

                hash_update($hmac, $cipher);
                fwrite($out, $cipher);
                $counter++;
            }

            fwrite($out, hash_final($hmac, true));
        } finally {
            fclose($in);
            fclose($out);
        }

        @chmod($destination, 0600);
    }

    public function decryptFile(string $source, string $destination): void
    {
        $this->verify($source);

        [$encKey] = $this->keys();

        $in = $this->open($source, 'rb');
        $out = $this->open($destination, 'wb');

        try {
            $header = fread($in, strlen(self::MAGIC) + 8);
            $nonce = substr($header, strlen(self::MAGIC), 8);

            $payload = filesize($source) - strlen($header) - self::HMAC_BYTES;
            $counter = 0;

            while ($payload > 0) {
                $read = (int) min(self::CHUNK, $payload);
                $cipher = fread($in, $read);

                if ($cipher === false || $cipher === '') {
                    break;
                }

                $plain = openssl_decrypt(
                    $cipher,
                    'aes-256-ctr',
                    $encKey,
                    OPENSSL_RAW_DATA,
                    $this->iv($nonce, $counter),
                );

                if ($plain === false) {
                    throw new RuntimeException('Dekripsi potongan backup gagal.');
                }

                fwrite($out, $plain);
                $payload -= strlen($cipher);
                $counter++;
            }
        } finally {
            fclose($in);
            fclose($out);
        }
    }

    /**
     * Memeriksa keaslian berkas tanpa menulis hasil dekripsi ke mana pun.
     */
    public function verify(string $source): bool
    {
        [, $macKey] = $this->keys();

        $size = filesize($source);
        $headerLength = strlen(self::MAGIC) + 8;

        if ($size === false || $size < $headerLength + self::HMAC_BYTES) {
            throw new RuntimeException('Berkas backup terpotong atau bukan berkas terenkripsi.');
        }

        $handle = $this->open($source, 'rb');

        try {
            $header = fread($handle, $headerLength);

            if (! str_starts_with((string) $header, self::MAGIC)) {
                throw new RuntimeException('Berkas ini bukan backup terenkripsi milik aplikasi.');
            }

            $hmac = hash_init('sha256', HASH_HMAC, $macKey);
            hash_update($hmac, $header);

            $remaining = $size - $headerLength - self::HMAC_BYTES;

            while ($remaining > 0) {
                $chunk = fread($handle, (int) min(self::CHUNK, $remaining));

                if ($chunk === false || $chunk === '') {
                    break;
                }

                hash_update($hmac, $chunk);
                $remaining -= strlen($chunk);
            }

            $expected = hash_final($hmac, true);
            $actual = (string) fread($handle, self::HMAC_BYTES);
        } finally {
            fclose($handle);
        }

        // hash_equals mencegah kebocoran informasi lewat selisih waktu banding.
        if (! hash_equals($expected, $actual)) {
            throw new RuntimeException('Backup gagal diverifikasi: berkas rusak atau kunci enkripsi salah.');
        }

        return true;
    }

    /**
     * @return array{0:string,1:string} kunci enkripsi dan kunci MAC
     */
    private function keys(): array
    {
        $master = (string) config('backup.encryption_key');

        if ($master === '') {
            throw new RuntimeException(
                'BACKUP_ENCRYPTION_KEY belum diisi. Jalankan: php artisan backup:key'
            );
        }

        $raw = base64_decode($master, true);
        $raw = $raw !== false && strlen($raw) >= 32 ? $raw : hash('sha256', $master, true);

        // Satu kunci induk diturunkan menjadi dua kunci berbeda; memakai kunci
        // yang sama untuk enkripsi dan MAC adalah kesalahan kriptografi klasik.
        return [
            hash_hkdf('sha256', $raw, 32, 'portfolio-backup-encryption'),
            hash_hkdf('sha256', $raw, 32, 'portfolio-backup-authentication'),
        ];
    }

    private function iv(string $nonce, int $counter): string
    {
        return $nonce.pack('J', $counter);
    }

    /**
     * @return resource
     */
    private function open(string $path, string $mode)
    {
        $handle = fopen($path, $mode);

        if ($handle === false) {
            throw new RuntimeException("Tidak dapat membuka berkas: {$path}");
        }

        return $handle;
    }
}
