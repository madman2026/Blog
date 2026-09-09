<?php

namespace Modules\User\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\User\Models\AuthorApplication;

final class AuthorApplications
{
    public function paginate(?string $status, int $perPage): LengthAwarePaginator
    {
        return AuthorApplication::query()
            ->with(['user.skills', 'reviewer'])
            ->when(filled($status), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }
}
