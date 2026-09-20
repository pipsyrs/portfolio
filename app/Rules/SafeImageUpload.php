<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Validasi gambar yang tidak bisa dibohongi dari sisi klien: tipe MIME dibaca
 * dari isi berkas, bukan dari header unggahan atau ekstensi nama berkas.
 */
class SafeImageUpload implements ValidationRule
{
    private const ALLOWED_MIMES = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
        'image/gif' => ['gif'],
    ];

    private const MAX_DIMENSION = 4000;

    public function __construct(private readonly int $maxKilobytes = 2048) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof TemporaryUploadedFile && ! $value instanceof UploadedFile) {
            $fail('Berkas :attribute tidak valid.');

            return;
        }

        if (! $value->isValid()) {
            $fail('Berkas :attribute gagal diunggah.');

            return;
        }

        if ($value->getSize() > $this->maxKilobytes * 1024) {
            $fail("Ukuran :attribute tidak boleh lebih dari {$this->maxKilobytes} KB.");

            return;
        }

        $path = $value->getRealPath();

        if (! is_string($path) || ! is_readable($path)) {
            $fail('Berkas :attribute tidak dapat dibaca.');

            return;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if (! is_string($mime) || ! array_key_exists($mime, self::ALLOWED_MIMES)) {
            $fail(':attribute harus berupa gambar JPG, PNG, WebP, atau GIF.');

            return;
        }

        // Nama berkas dengan ekstensi ganda (foto.php.jpg) ditolak.
        $name = $value instanceof TemporaryUploadedFile
            ? $value->getClientOriginalName()
            : (string) $value->getClientOriginalName();

        if (substr_count($name, '.') > 1) {
            $fail('Nama berkas :attribute tidak boleh mengandung ekstensi ganda.');

            return;
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (! in_array($extension, self::ALLOWED_MIMES[$mime], true)) {
            $fail('Ekstensi :attribute tidak cocok dengan isi berkas.');

            return;
        }

        $dimensions = @getimagesize($path);

        if ($dimensions === false) {
            $fail(':attribute bukan berkas gambar yang dapat dibaca.');

            return;
        }

        // Mencegah decompression bomb: gambar kecil terkompresi yang membengkak
        // menjadi ratusan megabyte saat di-decode.
        if ($dimensions[0] > self::MAX_DIMENSION || $dimensions[1] > self::MAX_DIMENSION) {
            $fail(':attribute melebihi '.self::MAX_DIMENSION.'px. Perkecil gambar terlebih dahulu.');
        }
    }
}
