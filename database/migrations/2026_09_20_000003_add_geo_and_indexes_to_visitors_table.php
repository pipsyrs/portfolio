<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lokasi di-resolve sekali saat kunjungan dicatat, bukan dipanggil ke API
 * eksternal per baris saat tabel dirender. Index ditambahkan karena chart
 * mengelompokkan berdasarkan visited_date.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('country')->nullable()->after('user_agent');
            $table->string('city')->nullable()->after('country');
            $table->boolean('is_bot')->default(false)->after('city');

            $table->index('visited_date');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex(['visited_date']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['country', 'city', 'is_bot']);
        });
    }
};
