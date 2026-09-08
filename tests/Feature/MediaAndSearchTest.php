<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    Storage::fake('public');
    Queue::fake();
});

test('an author can upload a validated featured image', function () {
    $author = User::factory()->create();
    $author->assignRole(UserRole::Author->value);
    $post = Post::factory()->for($author, 'author')->create();
    Sanctum::actingAs($author);

    $this->put('/api/v1/management/posts/'.$post->getKey().'/featured-image', [
        'image' => UploadedFile::fake()->image('featured.jpg', 1600, 900),
        'alt' => 'Laravel application architecture',
    ])->assertSuccessful()
        ->assertJsonPath('data.featured_image.original', fn (string $url): bool => str_contains($url, 'featured.jpg'));

    expect($post->refresh()->getFirstMedia('featured_image'))
        ->not->toBeNull()
        ->and($post->getFirstMedia('featured_image')?->getCustomProperty('alt'))
        ->toBe('Laravel application architecture');
});

test('post html is sanitized before persistence', function () {
    $author = User::factory()->create();
    $author->assignRole(UserRole::Author->value);
    Sanctum::actingAs($author);

    $response = $this->postJson('/api/v1/management/posts', [
        'type' => PostType::Article->value,
        'translations' => [[
            'locale' => 'en',
            'title' => 'Safe article',
            'body' => '<p onclick="alert(1)">Hello</p><script>alert(2)</script>',
        ]],
    ])->assertCreated();

    $body = Post::query()->findOrFail($response->json('data.id'))
        ->translations()
        ->sole()
        ->body;

    expect($body)->toContain('<p>Hello</p>')
        ->not->toContain('onclick')
        ->not->toContain('<script');
});

test('published translations are searchable by locale', function () {
    $post = Post::factory()->published()->create();
    PostTranslation::factory()->for($post)->create([
        'locale' => 'en',
        'title' => 'Modern Laravel queues',
        'summary' => 'A guide to Horizon and Redis workers.',
    ]);

    $this->getJson('/api/v1/en/search?q=Horizon')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $post->getKey());
});
