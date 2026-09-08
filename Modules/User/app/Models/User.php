<?php

namespace Modules\User\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Blog\Models\Post;
use Modules\Interaction\Models\Bookmark;
use Modules\Interaction\Models\Comment;
use Modules\Interaction\Models\Like;
use Modules\User\Database\Factories\UserFactory;
use Modules\User\Enums\UserStatus;
use Modules\User\Notifications\ResetPassword;
use Modules\User\Notifications\VerifyEmail;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(
    'username',
    'email',
    'phone',
    'password',
    'avatar_path',
    'bio',
    'about',
    'social_links',
    'preferred_locale',
    'status',
)]
#[Hidden(['password', 'remember_token'])]
#[RouteKey('username')]
class User extends Authenticatable implements HasLocalePreference, HasMedia, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use InteractsWithMedia;
    use Notifiable;
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'social_links' => 'array',
            'status' => UserStatus::class,
        ];
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class);
    }

    public function authorApplication(): HasOne
    {
        return $this->hasOne(AuthorApplication::class);
    }

    public function reviewedAuthorApplications(): HasMany
    {
        return $this->hasMany(AuthorApplication::class, 'reviewed_by');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function preferredLocale(): string
    {
        return $this->preferred_locale;
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.'.$this->getKey();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar_small')
            ->fit(Fit::Crop, 96, 96)
            ->format('webp')
            ->quality(84)
            ->performOnCollections('avatar');

        $this->addMediaConversion('avatar_large')
            ->fit(Fit::Crop, 320, 320)
            ->format('webp')
            ->quality(86)
            ->performOnCollections('avatar');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
