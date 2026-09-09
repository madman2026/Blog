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
use Modules\User\Queries\ManagedUsers;
use Modules\User\Transformers\UserResource;

class ManagedUserController extends Controller
{
    public function index(Request $request, ManagedUsers $managedUsers): AnonymousResourceCollection
    {
        Gate::authorize(UserPermission::UsersViewAny->value);

        $users = $managedUsers->paginate(
            $request->string('search')->toString(),
            $request->string('status')->toString(),
            min($request->integer('per_page', 20), 100),
        );

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
