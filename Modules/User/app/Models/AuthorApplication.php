<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Database\Factories\AuthorApplicationFactory;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Policies\AuthorApplicationPolicy;

#[Fillable('user_id', 'status', 'reviewed_by', 'reviewed_at', 'review_notes')]
#[UsePolicy(AuthorApplicationPolicy::class)]
class AuthorApplication extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => AuthorApplicationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected static function newFactory(): AuthorApplicationFactory
    {
        return AuthorApplicationFactory::new();
    }
}
