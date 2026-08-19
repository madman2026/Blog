<?php

namespace Modules\Interaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Interaction\Database\Factories\ViewFactory;
use Modules\User\Models\User;

#[Fillable('ip_address')]
class View extends Model
{
    use HasFactory;

    public function viewer(): BelongsTo
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
