<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Modules\User\Models\User;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BlogDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            PostSeeder::class,
            TagSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reset permission cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();


        /*
        |--------------------------------------------------------------------------
        | Find super user
        |--------------------------------------------------------------------------
        */

        $superUser = User::query()
            ->where('super_user', true)
            ->first();

        if (! $superUser) {
            throw new RuntimeException(
                'Super user not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Create roles & permissions
        |--------------------------------------------------------------------------
        */

        $roles = $this->createRoles();

        $this->createPermissions($roles);


        /*
        |--------------------------------------------------------------------------
        | Super user
        |--------------------------------------------------------------------------
        */

        $superUser->assignRole(
            $roles->get('admin')
        );


        /*
        |--------------------------------------------------------------------------
        | Reset permission cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }


    /**
     * Create blog roles.
     */
    private function createRoles(): Collection
    {
        $guard = config('auth.defaults.guard', 'web');

        return collect([

            'admin' => Role::query()->firstOrCreate([
                'name' => 'admin',
                'guard_name' => $guard,
            ]),

            'author' => Role::query()->firstOrCreate([
                'name' => 'author',
                'guard_name' => $guard,
            ]),

            'user' => Role::query()->firstOrCreate([
                'name' => 'user',
                'guard_name' => $guard,
            ]),

        ]);
    }


    /**
     * Create blog permissions and assign them to roles.
     */
    private function createPermissions(Collection $roles): void
    {
        $guard = config('auth.defaults.guard', 'web');


        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = collect([

            'posts.view',

            'posts.create',

            'posts.update',

            'posts.delete',

            'posts.publish',

        ])->mapWithKeys(
            fn (string $name) => [
                $name => Permission::query()->firstOrCreate([
                    'name' => $name,
                    'guard_name' => $guard,
                ]),
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        |
        | Admin has full access to Blog posts.
        |
        */

        $roles
            ->get('admin')
            ->syncPermissions(
                $permissions->values()
            );


        /*
        |--------------------------------------------------------------------------
        | Author
        |--------------------------------------------------------------------------
        |
        | Author can create and edit posts but cannot publish or delete them.
        |
        */

        $roles
            ->get('author')
            ->syncPermissions([
                $permissions->get('posts.view'),
                $permissions->get('posts.create'),
                $permissions->get('posts.update'),
            ]);


        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        |
        | Regular users don't have access to the Blog management panel.
        | Public blog pages don't need a Spatie permission.
        |
        */

        $roles
            ->get('user')
            ->syncPermissions([]);
    }
}
