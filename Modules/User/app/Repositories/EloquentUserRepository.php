<?php

namespace Modules\User\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\User\Data\UpdateProfileData;
use Modules\User\Enums\UserRole;
use Modules\User\Enums\UserStatus;
use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Models\Skill;
use Modules\User\Models\User;

final class EloquentUserRepository implements UserRepository
{
    public function register(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $user = User::query()->create($attributes);
            $user->assignRole(UserRole::User->value);

            return $user;
        });
    }

    public function findForAuthentication(string $field, string $identifier): ?User
    {
        return User::query()->where($field, $identifier)->first();
    }

    public function updateProfile(User $user, UpdateProfileData $data): User
    {
        DB::transaction(function () use ($user, $data): void {
            $user->update($data->attributes);

            if ($data->skills === null) {
                return;
            }

            $skillIds = collect($data->skills)
                ->map(function (string $name): int {
                    $slug = Str::slug($name) ?: 'skill-'.hash('xxh3', mb_strtolower($name));

                    return Skill::query()->firstOrCreate(
                        ['slug' => $slug],
                        ['name' => $name],
                    )->getKey();
                })
                ->all();

            $user->skills()->sync($skillIds);
        });

        return $user->refresh()->load(['skills', 'media']);
    }

    public function updatePassword(User $user, string $password): void
    {
        DB::transaction(function () use ($user, $password): void {
            $user->update(['password' => $password]);
            $user->tokens()->delete();
        });
    }

    public function resetPassword(User $user, string $password): void
    {
        DB::transaction(function () use ($user, $password): void {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();
            $user->tokens()->delete();
        });
    }

    public function updateRole(User $user, UserRole $role): User
    {
        $user->syncRoles([$role->value]);

        return $user->refresh();
    }

    public function updateStatus(User $user, UserStatus $status): User
    {
        return DB::transaction(function () use ($user, $status): User {
            $user->update(['status' => $status]);

            if ($status === UserStatus::Suspended) {
                $user->tokens()->delete();
            }

            return $user->refresh();
        });
    }
}
