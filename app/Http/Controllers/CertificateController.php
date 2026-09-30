<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CertificateController extends Controller
{
    private const ALLOWED_MIMES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function show(int $index): Response
    {
        $certifications = User::owner()?->certifications;
        $file = is_array($certifications) ? ($certifications[$index]['file'] ?? null) : null;

        abort_unless(is_string($file) && $file !== '', 404);

        $disk = Storage::disk('private');

        // Berkas lama mungkin masih berada di disk publik.
        if (! $disk->exists($file)) {
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($file), 404);

        $contents = $disk->get($file);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);

        // Hanya PDF/gambar asli yang disajikan inline; tipe lain ditolak.
        abort_unless(is_string($mime) && isset(self::ALLOWED_MIMES[$mime]), 404);

        $filename = 'Certificate_'.($index + 1).'.'.self::ALLOWED_MIMES[$mime];

        return response($contents, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "frame-ancestors 'self'",
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
