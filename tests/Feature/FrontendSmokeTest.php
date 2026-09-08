<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('public pages render successfully', function () {
    $author = User::factory()->create();
    $post = Post::factory()->published()->for($author, 'author')->create();
    $translation = PostTranslation::factory()->for($post)->create();

    $this->get(route('home'))->assertSuccessful();
    $this->get(route('blog.posts.index'))->assertSuccessful();
    $this->get(route('blog.posts.show', $translation))->assertSuccessful();
    $this->get(route('user.profile', $author))->assertSuccessful();
    $this->get(route('auth.login'))->assertSuccessful();
    $this->get(route('auth.register'))->assertSuccessful();
    $this->get(route('auth.forget-password'))->assertSuccessful();
    $this->get(route('password.reset', ['token' => 'test-token', 'email' => $author->email]))->assertSuccessful();
});

test('authenticated workspace pages render by role', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::User->value);

    $this->actingAs($user);
    $this->get(route('dashboard'))->assertSuccessful();
    $this->get(route('notifications.index'))->assertSuccessful();
    $this->get(route('user.settings'))->assertSuccessful();
    $this->get(route('user.change-password'))->assertSuccessful();

    $author = User::factory()->create();
    $author->assignRole(UserRole::Author->value);

    $this->actingAs($author);
    $this->get(route('blog.manage.posts.index'))->assertSuccessful();
    $this->get(route('blog.manage.posts.create'))->assertSuccessful();
});
