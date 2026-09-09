<?php

namespace Modules\User\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Modules\User\Actions\UpdateManagedUserRole;
use Modules\User\Actions\UpdateManagedUserStatus;
use Modules\User\Enums\UserPermission;
use Modules\User\Enums\UserRole;
use Modules\User\Enums\UserStatus;
use Modules\User\Http\Requests\Api\V1\UpdateUserRolesRequest;
use Modules\User\Http\Requests\Api\V1\UpdateUserStatusRequest;
use Modules\User\Models\User;
use Modules\User\Transformers\UserResource;

class ManagedUserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize(UserPermission::UsersViewAny->value);

        $users = User::query()
            ->when(
                $request->string('search')->isNotEmpty(),
                fn ($query) => $query->where(function ($query) use ($request): void {
                    $search = $request->string('search')->toString();
                    $query->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                }),
            )
            ->when(
                $request->string('status')->isNotEmpty(),
                fn ($query) => $query->where('status', $request->string('status')->toString()),
            )
            ->with(['skills', 'media', 'roles'])
            ->latest()
            ->paginate(min($request->integer('per_page', 20), 100));

        return UserResource::collection($users);
    }

    public function show(User $managedUser): UserResource
    {
        Gate::authorize(UserPermission::UsersViewAny->value);

        return new UserResource($managedUser->load(['skills', 'media', 'roles']));
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $managedUser, UpdateManagedUserStatus $updateStatus): UserResource
    {
        $updateStatus->handle($managedUser, $request->user(), UserStatus::from($request->validated('status')));

        return new UserResource($managedUser->load(['skills', 'media', 'roles']));
    }

    public function updateRole(UpdateUserRolesRequest $request, User $managedUser, UpdateManagedUserRole $updateRole): UserResource
    {
        $updateRole->handle($managedUser, $request->user(), UserRole::from($request->validated('role')));

        return new UserResource($managedUser->load(['skills', 'media', 'roles']));
    }
}
