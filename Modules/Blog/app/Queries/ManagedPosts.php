<?php

namespace Modules\Blog\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Blog\Models\Post;
use Modules\User\Enums\UserPermission;
use Modules\User\Models\User;

final class ManagedPosts
{
    public function paginate(User $actor, ?string $status, int $perPage): LengthAwarePaginator
    {
        return Post::query()
            ->when(
                ! $actor->can(UserPermission::PostsUpdateAny->value),
                fn ($query) => $query->whereBelongsTo($actor, 'author'),
            )
            ->when(filled($status), fn ($query) => $query->where('status', $status))
            ->with(['author.skills', 'author.media', 'translations', 'categories.translations', 'tags.translations', 'media'])
            ->withCount(['comments', 'likes', 'views'])
            ->latest()
            ->paginate($perPage);
    }
}
