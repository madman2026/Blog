<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserRole;
use Modules\User\Enums\UserStatus;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('panel routes enforce their role capabilities', function () {
    $member = User::factory()->create();
    $member->assignRole(UserRole::User->value);
    $author = User::factory()->create();
    $author->assignRole(UserRole::Author->value);
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $this->get(route('writer.dashboard'))->assertRedirect(route('auth.login'));
    $this->actingAs($member)->get(route('writer.dashboard'))->assertForbidden();
    $this->actingAs($author)->get(route('writer.dashboard'))->assertOk();
    $this->actingAs($author)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($admin)->get(route('admin.comments'))->assertOk();
    $this->actingAs($admin)->get(route('admin.applications'))->assertOk();
    $this->actingAs($admin)->get(route('admin.taxonomies'))->assertOk();
    $this->actingAs($admin)->get(route('admin.users'))->assertOk();
});

test('writer dashboard reports only the signed in authors work', function () {
    $author = User::factory()->create();
    $author->assignRole(UserRole::Author->value);
    $otherAuthor = User::factory()->create();
    $otherAuthor->assignRole(UserRole::Author->value);

    $draft = Post::factory()->for($author, 'author')->create();
    PostTranslation::factory()->for($draft)->create(['title' => 'Own draft']);
    Post::factory()->for($otherAuthor, 'author')->published()->create();

    Livewire::actingAs($author)
        ->test('writer.dashboard')
        ->assertSee('Own draft')
        ->assertSet('metrics.all', 1)
        ->assertSet('metrics.draft', 1)
        ->assertSet('metrics.published', 0);
});

test('admin can moderate comments and approve author applications from dedicated queues', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $comment = Comment::factory()->create();
    $application = AuthorApplication::factory()->create();

    Livewire::actingAs($admin)
        ->test('admin.comments')
        ->call('openDecision', $comment->getKey(), CommentStatus::Approved->value)
        ->call('saveDecision')
        ->assertHasNoErrors();

    Livewire::actingAs($admin)
        ->test('admin.applications')
        ->call('openDecision', $application->getKey(), AuthorApplicationStatus::Approved->value)
        ->call('saveDecision')
        ->assertHasNoErrors();

    expect($comment->refresh()->status)->toBe(CommentStatus::Approved)
        ->and($application->refresh()->status)->toBe(AuthorApplicationStatus::Approved)
        ->and($application->user->hasRole(UserRole::Author->value))->toBeTrue();
});

test('admin can maintain bilingual taxonomies', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    Livewire::actingAs($admin)
        ->test('admin.taxonomies')
        ->set('kind', 'category')
        ->set('faName', 'مهندسی نرم افزار')
        ->set('faSlug', 'software-engineering-fa')
        ->set('enName', 'Software Engineering')
        ->set('enSlug', 'software-engineering')
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::query()->with('translations')->firstOrFail();

    expect($category->translations)->toHaveCount(2)
        ->and($category->translations->pluck('locale')->all())->toContain('fa', 'en');
});

test('admin can suspend accounts while only super users can change roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);
    $superUser = User::factory()->create();
    $superUser->assignRole(UserRole::SuperUser->value);
    $member = User::factory()->create();
    $member->assignRole(UserRole::User->value);

    Livewire::actingAs($admin)
        ->test('admin.users')
        ->call('changeStatus', $member->getKey(), UserStatus::Suspended->value)
        ->assertHasNoErrors()
        ->call('changeRole', $member->getKey(), UserRole::Author->value)
        ->assertForbidden();

    Livewire::actingAs($superUser)
        ->test('admin.users')
        ->call('changeRole', $member->getKey(), UserRole::Author->value)
        ->assertHasNoErrors();

    expect($member->refresh()->status)->toBe(UserStatus::Suspended)
        ->and($member->hasRole(UserRole::Author->value))->toBeTrue();
});
