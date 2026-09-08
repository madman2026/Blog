<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserRole;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\Skill;
use Modules\User\Models\User;
use Modules\User\Notifications\AuthorApplicationReviewed;
use Modules\User\Notifications\AuthorApplicationSubmitted;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    Notification::fake();
});

test('a user with a complete profile can apply and be approved as an author', function () {
    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin->value);

    $skill = Skill::factory()->create();
    $user = User::factory()->create([
        'avatar_path' => 'avatars/user.webp',
        'bio' => 'Backend developer',
        'about' => 'I write about modern Laravel applications.',
        'social_links' => ['github' => 'https://github.com/example'],
    ]);
    $user->assignRole(UserRole::User->value);
    $user->skills()->attach($skill);

    Sanctum::actingAs($user);
    $this->postJson('/api/v1/author-applications')->assertSuccessful();

    $application = AuthorApplication::query()->firstOrFail();
    expect($application->status)->toBe(AuthorApplicationStatus::Pending);
    Notification::assertSentTo($admin, AuthorApplicationSubmitted::class);

    Sanctum::actingAs($admin);

    $this->patchJson('/api/v1/management/author-applications/'.$application->getKey().'/review', [
        'status' => AuthorApplicationStatus::Approved->value,
    ])->assertSuccessful()->assertJsonPath('data.status', AuthorApplicationStatus::Approved->value);

    expect($user->fresh()->hasRole(UserRole::Author->value))->toBeTrue();
    Notification::assertSentTo($user, AuthorApplicationReviewed::class);
});

test('an incomplete profile cannot apply for authorship', function () {
    $user = User::factory()->create();
    $user->assignRole(UserRole::User->value);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/author-applications')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('profile');
});
