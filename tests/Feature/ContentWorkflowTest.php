<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Enums\ReviewDecision;
use Modules\Blog\Models\Post;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();
});

test('an author can create and submit a bilingual post for admin publication', function () {
    $author = User::factory()->create();
    $author->assignRole(UserRole::Author->value);
    Sanctum::actingAs($author);

    $response = $this->postJson('/api/v1/management/posts', [
        'type' => PostType::Article->value,
        'translations' => [
            [
                'locale' => 'fa',
                'title' => 'راهنمای معماری لاراول',
                'summary' => 'خلاصه فارسی',
                'body' => '<p>محتوای فارسی</p>',
            ],
            [
                'locale' => 'en',
                'title' => 'Laravel architecture guide',
                'summary' => 'English summary',
                'body' => '<p>English content</p>',
            ],
        ],
    ])->assertCreated();

    $post = Post::query()->findOrFail($response->json('data.id'));
    expect($post->translations()->count())->toBe(2);

    $this->postJson('/api/v1/management/posts/'.$post->getKey().'/submit')
        ->assertSuccessful()
        ->assertJsonPath('data.status', PostStatus::PendingReview->value);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    Sanctum::actingAs($admin);

    $this->patchJson('/api/v1/management/posts/'.$post->getKey().'/review', [
        'decision' => ReviewDecision::Publish->value,
    ])->assertSuccessful()->assertJsonPath('data.status', PostStatus::Published->value);

    $translation = $post->translations()->where('locale', 'fa')->firstOrFail();

    $this->getJson('/api/v1/fa/posts/'.$translation->slug)
        ->assertSuccessful()
        ->assertJsonPath('data.id', $post->getKey());
});

test('an author cannot update another authors post', function () {
    $owner = User::factory()->create();
    $owner->assignRole(UserRole::Author->value);
    $post = Post::factory()->for($owner, 'author')->create();

    $otherAuthor = User::factory()->create();
    $otherAuthor->assignRole(UserRole::Author->value);
    Sanctum::actingAs($otherAuthor);

    $this->patchJson('/api/v1/management/posts/'.$post->getKey(), [
        'type' => PostType::News->value,
        'translations' => [[
            'locale' => 'en',
            'title' => 'Forbidden update',
            'body' => 'No access',
        ]],
    ])->assertForbidden();
});

test('removing an optional translation deletes the stale translation', function () {
    $author = User::factory()->create();
    $author->assignRole(UserRole::Author->value);
    Sanctum::actingAs($author);

    $response = $this->postJson('/api/v1/management/posts', [
        'type' => PostType::Article->value,
        'translations' => [
            ['locale' => 'fa', 'title' => 'نسخه فارسی', 'body' => '<p>متن</p>'],
            ['locale' => 'en', 'title' => 'English version', 'body' => '<p>Body</p>'],
        ],
    ])->assertCreated();

    $post = Post::query()->findOrFail($response->json('data.id'));

    $this->patchJson('/api/v1/management/posts/'.$post->getKey(), [
        'type' => PostType::Article->value,
        'translations' => [
            ['locale' => 'fa', 'title' => 'فقط فارسی', 'body' => '<p>متن تازه</p>'],
        ],
    ])->assertSuccessful();

    expect($post->translations()->pluck('locale')->all())->toBe(['fa']);
});
