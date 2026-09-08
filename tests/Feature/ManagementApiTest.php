<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\UserRole;
use Modules\User\Enums\UserStatus;
use Modules\User\Models\User;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('an admin can manage bilingual taxonomies and they are available publicly', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    Sanctum::actingAs($admin);

    $categoryResponse = $this->postJson('/api/v1/management/categories', [
        'is_active' => true,
        'translations' => [
            ['locale' => 'fa', 'name' => 'برنامه‌نویسی', 'slug' => 'برنامه-نویسی'],
            ['locale' => 'en', 'name' => 'Programming', 'slug' => 'programming'],
        ],
    ])->assertCreated()->assertJsonFragment(['slug' => 'programming']);

    $this->postJson('/api/v1/management/tags', [
        'translations' => [
            ['locale' => 'fa', 'name' => 'لاراول', 'slug' => 'لاراول'],
            ['locale' => 'en', 'name' => 'Laravel', 'slug' => 'laravel'],
        ],
    ])->assertCreated();

    $this->getJson('/api/v1/fa/categories')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $categoryResponse->json('data.id'));

    $this->getJson('/api/v1/en/tags')
        ->assertSuccessful()
        ->assertJsonFragment(['slug' => 'laravel']);
});

test('a regular user cannot manage taxonomies', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::User->value);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/management/tags', [
        'translations' => [
            ['locale' => 'en', 'name' => 'Blocked', 'slug' => 'blocked'],
        ],
    ])->assertForbidden();
});

test('an admin can inspect and suspend users but protected accounts stay active', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $target = User::factory()->create();
    $target->assignRole(UserRole::User->value);
    $target->createToken('phone');
    $superUser = User::factory()->create();
    $superUser->assignRole(UserRole::SuperUser->value);
    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/management/users')
        ->assertSuccessful()
        ->assertJsonFragment(['email' => $target->email]);

    $this->patchJson('/api/v1/management/users/'.$target->username.'/status', [
        'status' => UserStatus::Suspended->value,
    ])->assertSuccessful()->assertJsonPath('data.status', UserStatus::Suspended->value);

    expect($target->refresh()->status)->toBe(UserStatus::Suspended)
        ->and($target->tokens()->count())->toBe(0);

    $this->patchJson('/api/v1/management/users/'.$admin->username.'/status', [
        'status' => UserStatus::Suspended->value,
    ])->assertUnprocessable();

    $this->patchJson('/api/v1/management/users/'.$superUser->username.'/status', [
        'status' => UserStatus::Suspended->value,
    ])->assertUnprocessable();
});

test('a user can paginate and read only their own notifications', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Sanctum::actingAs($user);

    $notification = $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'TestNotification',
        'data' => ['message' => 'Ready'],
    ]);
    $otherNotification = $otherUser->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'OtherNotification',
        'data' => ['message' => 'Private'],
    ]);

    $this->getJson('/api/v1/notifications?unread=1')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonMissing(['id' => $otherNotification->getKey()]);

    $this->patchJson('/api/v1/notifications/'.$notification->getKey().'/read')
        ->assertSuccessful()
        ->assertJsonPath('data.id', $notification->getKey());

    expect($notification->refresh()->read_at)->not->toBeNull();
});

test('only a super-user can change another users role', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $target = User::factory()->create();
    $target->assignRole(UserRole::User->value);
    Sanctum::actingAs($admin);

    $this->patchJson('/api/v1/management/users/'.$target->username.'/role', [
        'role' => UserRole::Author->value,
    ])->assertForbidden();

    $superUser = User::factory()->create();
    $superUser->assignRole(UserRole::SuperUser->value);
    Sanctum::actingAs($superUser);

    $this->patchJson('/api/v1/management/users/'.$target->username.'/role', [
        'role' => UserRole::Admin->value,
    ])->assertSuccessful()->assertJsonPath('data.roles.0', UserRole::Admin->value);

    expect($target->refresh()->hasRole(UserRole::Admin->value))->toBeTrue();
});

test('a user can paginate bookmarked posts for a requested locale', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $translation = PostTranslation::factory()->for($post)->create(['locale' => 'fa']);
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/fa/posts/'.$translation->slug.'/bookmark')->assertSuccessful();

    $this->getJson('/api/v1/fa/bookmarks')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $post->getKey());
});
