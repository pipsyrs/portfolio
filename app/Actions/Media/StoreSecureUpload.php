<?php

namespace App\Actions\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Menyimpan unggahan ke disk privat dengan nama acak. Gambar di-decode ulang
 * dan ditulis kembali oleh GD — ini membuang seluruh metadata (termasuk GPS
 * pada foto) sekaligus menetralkan berkas polyglot yang menyelipkan kode di
 * dalam segmen gambar.
 */
class StoreSecureUpload
{
    public function image(TemporaryUploadedFile|UploadedFile $file, string $directory): string
    {
        $source = $file->getRealPath();
        $info = @getimagesize($source);

        if ($info === false) {
            throw new \RuntimeException('Berkas gambar tidak dapat dibaca.');
        }

        $image = match ($info['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png' => @imagecreatefrompng($source),
            'image/webp' => @imagecreatefromwebp($source),
            'image/gif' => @imagecreatefromgif($source),
            default => false,
        };

        if ($image === false) {
            throw new \RuntimeException('Format gambar tidak didukung.');
        }

        // GIF animasi kehilangan animasinya kalau di-decode ulang, jadi berkas
        // GIF disimpan apa adanya setelah lolos validasi isi.
        if ($info['mime'] === 'image/gif') {
            imagedestroy($image);

            return $this->putRaw($file, $directory, 'gif');
        }

        $extension = $info['mime'] === 'image/png' ? 'png' : 'webp';
        $path = trim($directory, '/').'/'.Str::random(40).'.'.$extension;
        $temp = tempnam(sys_get_temp_dir(), 'img');

        try {
            if ($extension === 'png') {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                $ok = imagepng($image, $temp, 6);
            } else {
                $ok = imagewebp($image, $temp, 82);
            }

            if (! $ok) {
                throw new \RuntimeException('Gagal memproses ulang gambar.');
            }

            Storage::disk('private')->put($path, (string) file_get_contents($temp), 'private');
        } finally {
            imagedestroy($image);

            if (is_string($temp) && file_exists($temp)) {
                unlink($temp);
            }
        }

        return $path;
    }

    public function document(TemporaryUploadedFile|UploadedFile $file, string $directory): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $extension = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'bin';

        return $this->putRaw($file, $directory, $extension);
    }

    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $disk = Storage::disk('private');

        if ($disk->exists($path)) {
            $disk->delete($path);

            return;
        }

        // Berkas lama dari sebelum pemindahan ke disk privat.
        $public = Storage::disk('public');

        if ($public->exists($path)) {
            $public->delete($path);
        }
    }

    private function putRaw(TemporaryUploadedFile|UploadedFile $file, string $directory, string $extension): string
    {
        $path = trim($directory, '/').'/'.Str::random(40).'.'.$extension;

        Storage::disk('private')->put($path, (string) file_get_contents($file->getRealPath()), 'private');

        return $path;
    }
}
