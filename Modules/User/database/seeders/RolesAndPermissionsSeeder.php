<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Enums\UserPermission;
use Modules\User\Enums\UserRole;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(UserPermission::cases())
            ->mapWithKeys(fn (UserPermission $permission): array => [
                $permission->value => Permission::findOrCreate($permission->value, 'web'),
            ]);

        $user = Role::findOrCreate(UserRole::User->value, 'web');
        $author = Role::findOrCreate(UserRole::Author->value, 'web');
        $admin = Role::findOrCreate(UserRole::Admin->value, 'web');
        $superUser = Role::findOrCreate(UserRole::SuperUser->value, 'web');

        $user->syncPermissions([]);

        $author->syncPermissions($permissions->only([
            UserPermission::DashboardView->value,
            UserPermission::PostsViewAny->value,
            UserPermission::PostsCreate->value,
            UserPermission::PostsUpdateOwn->value,
            UserPermission::PostsDeleteOwn->value,
            UserPermission::PostsSubmit->value,
        ])->values());

        $admin->syncPermissions(
            $permissions->except(UserPermission::RolesManage->value)->values(),
        );

        $superUser->syncPermissions($permissions->values());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
