<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan disimpan sebagai pasangan key/value, bukan satu kolom per opsi.
 * Menambah opsi baru cukup mendaftarkannya di config/settings.php tanpa
 * migrasi tambahan.
 *
 * Nilai rahasia (SMTP, API key) ditulis dalam bentuk ciphertext — lihat
 * daftar `secret` di config/settings.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
