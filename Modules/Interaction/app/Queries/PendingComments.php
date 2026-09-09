<?php

namespace Modules\Interaction\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;

final class PendingComments
{
    public function paginate(int $perPage): LengthAwarePaginator
    {
        return Comment::query()
            ->where('status', CommentStatus::Pending)
            ->with(['user.skills', 'commentable'])
            ->oldest()
            ->paginate($perPage);
    }
}
