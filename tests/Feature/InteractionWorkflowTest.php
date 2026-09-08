<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;
use Modules\Interaction\Notifications\ApprovedCommentAdded;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();
});

test('an authenticated user can comment and an admin can approve it', function () {
    $post = Post::factory()->published()->create();
    $translation = PostTranslation::factory()->for($post)->create(['locale' => 'en']);
    $user = User::factory()->create();
    $user->assignRole(UserRole::User->value);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/en/posts/'.$translation->slug.'/comments', [
        'body' => 'A useful article.',
    ])->assertSuccessful()->assertJsonPath('data.status', CommentStatus::Pending->value);

    $comment = Comment::query()->firstOrFail();
    $this->getJson('/api/v1/en/posts/'.$translation->slug.'/comments')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    Sanctum::actingAs($admin);

    $this->patchJson('/api/v1/management/comments/'.$comment->getKey().'/moderate', [
        'status' => CommentStatus::Approved->value,
    ])->assertSuccessful();

    Notification::assertSentTo($post->author, ApprovedCommentAdded::class);

    $this->getJson('/api/v1/en/posts/'.$translation->slug.'/comments')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('likes and bookmarks toggle without duplicates', function () {
    $post = Post::factory()->published()->create();
    $translation = PostTranslation::factory()->for($post)->create(['locale' => 'fa']);
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/v1/fa/posts/'.$translation->slug.'/like')
        ->assertSuccessful()
        ->assertJsonPath('liked', true);
    $this->putJson('/api/v1/fa/posts/'.$translation->slug.'/like')
        ->assertSuccessful()
        ->assertJsonPath('liked', false);

    $this->putJson('/api/v1/fa/posts/'.$translation->slug.'/bookmark')
        ->assertSuccessful()
        ->assertJsonPath('bookmarked', true);
    $this->putJson('/api/v1/fa/posts/'.$translation->slug.'/bookmark')
        ->assertSuccessful()
        ->assertJsonPath('bookmarked', false);

    expect($post->likes()->count())->toBe(0)
        ->and($post->bookmarks()->count())->toBe(0);
});
