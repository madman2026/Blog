<?php

namespace Modules\Interaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Interaction\Database\Factories\CommentFactory;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Policies\CommentPolicy;
use Modules\User\Models\User;

#[Fillable(
    'user_id',
    'parent_id',
    'commentable_type',
    'commentable_id',
    'body',
    'status',
    'moderated_by',
    'moderated_at',
    'moderation_notes',
)]
#[UsePolicy(CommentPolicy::class)]
class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'moderated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    #[Scope]
    protected function approved(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Approved);
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }
}
