<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris = satu pengaturan. Skema, cast, dan daftar rahasianya ada di
 * config/settings.php; pembacaan/penulisan sehari-hari lewat App\Support\Settings
 * (helper `settings()`), bukan model ini langsung.
 */
class Setting extends Model
{
    use HasUuids;

    protected $table = 'settings';

    protected $fillable = ['key', 'value'];
}
