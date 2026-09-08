<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Blog\Database\Seeders\BlogDatabaseSeeder;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $superUser = User::query()->updateOrCreate(
            ['email' => config('platform.super_user.email')],
            [
                'username' => config('platform.super_user.username'),
                'phone' => config('platform.super_user.phone'),
                'password' => config('platform.super_user.password'),
                'email_verified_at' => now(),
                'phone_verified_at' => config('platform.super_user.phone') ? now() : null,
                'preferred_locale' => config('platform.default_locale'),
            ],
        );

        $superUser->syncRoles(UserRole::SuperUser->value);

        $this->call(BlogDatabaseSeeder::class);
    }
}
