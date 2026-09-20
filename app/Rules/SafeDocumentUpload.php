<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Untuk CV dan berkas sertifikat: PDF diperiksa lewat magic bytes, gambar
 * lewat getimagesize. Tidak ada tipe lain yang boleh masuk.
 */
class SafeDocumentUpload implements ValidationRule
{
    private const ALLOWED_MIMES = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private readonly int $maxKilobytes = 5120,
        private readonly bool $pdfOnly = false,
    ) {}

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

        if (substr_count((string) $value->getClientOriginalName(), '.') > 1) {
            $fail('Nama berkas :attribute tidak boleh mengandung ekstensi ganda.');

            return;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $allowed = $this->pdfOnly ? ['application/pdf'] : self::ALLOWED_MIMES;

        if (! is_string($mime) || ! in_array($mime, $allowed, true)) {
            $fail($this->pdfOnly
                ? ':attribute harus berupa berkas PDF.'
                : ':attribute harus berupa PDF atau gambar (JPG, PNG, WebP).');

            return;
        }

        if ($mime === 'application/pdf') {
            $header = (string) file_get_contents($path, false, null, 0, 5);

            if (! str_starts_with($header, '%PDF-')) {
                $fail(':attribute bukan berkas PDF yang sah.');
            }

            return;
        }

        if (@getimagesize($path) === false) {
            $fail(':attribute bukan berkas gambar yang dapat dibaca.');
        }
    }
}
