<?php

namespace Modules\Interaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Interaction\Database\Factories\CommentFactory;
use Modules\User\Models\User;

#[Fillable('commentable_type', 'commentable_id', 'body', 'user_id', 'ip_address')]
class Comment extends Model
{
    use HasFactory , softDeletes;

    public function commenter(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }
}
