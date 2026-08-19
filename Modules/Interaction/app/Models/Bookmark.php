<?php

namespace Modules\Interaction\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Interaction\Database\Factories\BookmarkFactory;
use Modules\User\Models\User;

class Bookmark extends Model
{
    use HasFactory;

    public function bookmarkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): BookmarkFactory
    {
        return BookmarkFactory::new();
    }
}
