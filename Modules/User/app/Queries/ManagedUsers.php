<?php

namespace Modules\User\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\User\Models\User;

final class ManagedUsers
{
    public function paginate(?string $search, ?string $status, int $perPage): LengthAwarePaginator
    {
        return User::query()
            ->when(filled($search), function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(filled($status), fn ($query) => $query->where('status', $status))
            ->with(['skills', 'media', 'roles'])
            ->latest()
            ->paginate($perPage);
    }
}
