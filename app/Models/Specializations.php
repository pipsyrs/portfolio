<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specializations extends Model
{
    use HasUuids;

    protected $table = 'specializations';

    protected $fillable = [
        'name',
        'icon',
    ];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Projects::class, 'project_specialization', 'specialization_id', 'project_id');
    }
}
