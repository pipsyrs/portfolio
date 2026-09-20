<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SecureFileController extends Controller
{
    /**
     * Mengunduh satu berkas backup. Route sudah dilindungi auth + owner +
     * signed URL; validasi nama dan penyelesaian jalur dipusatkan di service
     * supaya aturannya tidak pernah menyimpang antara unduh, hapus, dan verifikasi.
     */
    public function backup(Request $request, string $file, DatabaseBackupService $service): BinaryFileResponse
    {
        $path = $service->path($file);

        abort_if($path === null, 404);

        return response()->download($path, $file, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
