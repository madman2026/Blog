<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\User\Database\Factories\SkillFactory;

#[Fillable('name', 'slug')]
class Skill extends Model
{
    use HasFactory;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    protected static function newFactory(): SkillFactory
    {
        return SkillFactory::new();
    }
}
