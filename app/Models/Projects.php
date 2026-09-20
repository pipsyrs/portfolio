<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Projects extends Model
{
    use HasUuids;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'description',
        'image',
        'url',
        'github_link',
    ];

    public function techStacks(): BelongsToMany
    {
        return $this->belongsToMany(TechStacks::class, 'project_tech_stack', 'project_id', 'tech_stack_id');
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specializations::class, 'project_specialization', 'project_id', 'specialization_id');
    }
}
