<?php

namespace Modules\Interaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Interaction\Database\Factories\LikeFactory;
use Modules\User\Models\User;

#[Fillable('user_id', 'likeable_type', 'likeable_id')]
class Like extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): LikeFactory
    {
        return LikeFactory::new();
    }
}
