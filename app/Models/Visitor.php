<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasUuids;

    protected $fillable = [
        'ip_address',
        'visited_date',
        'user_agent',
    ];
}
