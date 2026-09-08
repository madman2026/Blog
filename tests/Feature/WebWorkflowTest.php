<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\Blog\Models\Tag;
use Modules\Blog\Models\TagTranslation;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('a visitor can register through the web interface', function () {
    Notification::fake();

    Livewire::test('auth::pages.register')
        ->set('form.username', 'web_member')
        ->set('form.email', 'web@example.com')
        ->set('form.phone', '+989121234567')
        ->set('form.password', 'Strong-password-123')
        ->set('form.password_confirmation', 'Strong-password-123')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $user = User::query()->where('email', 'web@example.com')->firstOrFail();

    expect($user->hasRole(UserRole::User->value))->toBeTrue()
        ->and(auth()->id())->toBe($user->getKey());
});

test('an author can save a sanitized draft through the studio', function () {
    $author = User::factory()->create();
    $author->assignRole(UserRole::Author->value);

    Livewire::actingAs($author)
        ->test('blog::post.post')
        ->set('type', 'article')
        ->set('faTitle', 'راهنمای عملی لاراول')
        ->set('faSummary', 'یک خلاصه کوتاه')
        ->set('faBody', '<p>محتوای سالم</p><script>alert(1)</script>')
        ->call('save')
        ->assertHasNoErrors();

    $post = Post::query()->whereBelongsTo($author, 'author')->firstOrFail();

    expect($post->translations()->firstOrFail()->body)
        ->toContain('<p>محتوای سالم</p>')
        ->not->toContain('<script>');
});

test('a user can change their password and revoke API tokens', function () {
    $user = User::factory()->create();
    $user->createToken('old-device');

    Livewire::actingAs($user)
        ->test('user::pages.change-password')
        ->set('form.currentPassword', 'password')
        ->set('form.password', 'New-strong-password-123')
        ->set('form.passwordConfirmation', 'New-strong-password-123')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('New-strong-password-123', $user->refresh()->password))->toBeTrue()
        ->and($user->tokens()->count())->toBe(0);
});

test('optional profile fields can be saved empty', function () {
    $user = User::factory()->create([
        'bio' => 'Previous bio',
        'about' => 'Previous about',
        'social_links' => ['website' => 'https://example.com'],
    ]);

    Livewire::actingAs($user)
        ->test('user::pages.settings')
        ->set('bio', '')
        ->set('about', '')
        ->set('skills', '')
        ->set('socialLinks.website', '')
        ->set('socialLinks.github', '')
        ->set('socialLinks.linkedin', '')
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->bio)->toBeNull()
        ->and($user->about)->toBeNull()
        ->and($user->social_links)->toBe([])
        ->and($user->skills()->count())->toBe(0);
});

test('article interactions keep translated taxonomies eager loaded after livewire hydration', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $translation = PostTranslation::factory()->for($post)->create(['locale' => 'fa']);
    $tag = Tag::factory()->create();
    TagTranslation::factory()->for($tag)->create(['locale' => 'fa']);
    $post->tags()->attach($tag);

    Livewire::actingAs($user)
        ->test('blog::post.show', ['postTranslation' => $translation])
        ->call('toggleLike')
        ->assertHasNoErrors()
        ->call('toggleBookmark')
        ->assertHasNoErrors()
        ->set('comment', 'نظر آزمایشی برای مقاله')
        ->call('submitComment')
        ->assertHasNoErrors();

    expect($post->likes()->whereBelongsTo($user)->exists())->toBeTrue()
        ->and($post->bookmarks()->whereBelongsTo($user)->exists())->toBeTrue()
        ->and($post->comments()->whereBelongsTo($user)->exists())->toBeTrue();
});
