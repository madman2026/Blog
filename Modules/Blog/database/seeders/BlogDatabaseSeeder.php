<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;

class BlogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            TagSeeder::class,
            PostSeeder::class,
        ]);
    }
}
