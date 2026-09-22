<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CvController extends Controller
{
    public function show(): Response
    {
        $user = User::owner();

        abort_if(! $user || ! $user->cv_file, 404);

        $disk = Storage::disk('private');

        // Berkas lama mungkin masih berada di disk publik sebelum pemindahan.
        if (! $disk->exists($user->cv_file)) {
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($user->cv_file), 404);

        $contents = $disk->get($user->cv_file);

        // Hanya PDF asli yang boleh disajikan inline; berkas lain akan dipaksa
        // diunduh oleh browser sebagai oktet biasa.
        abort_unless(str_starts_with($contents, '%PDF-'), 404);

        $filename = 'CV_'.preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $user->name).'.pdf';

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "frame-ancestors 'self'",
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
