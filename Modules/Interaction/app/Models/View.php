<?php

namespace Modules\Interaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Interaction\Database\Factories\ViewFactory;
use Modules\User\Models\User;

#[Fillable('user_id', 'viewable_type', 'viewable_id', 'visitor_hash', 'viewed_on')]
class View extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['viewed_on' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): ViewFactory
    {
        return ViewFactory::new();
    }
}
