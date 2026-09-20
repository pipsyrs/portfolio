<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TechStacks extends Model
{
    use HasUuids;

    protected $table = 'tech_stacks';

    protected $fillable = [
        'name',
        'icon',
    ];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Projects::class, 'project_tech_stack', 'tech_stack_id', 'project_id');
    }
}
