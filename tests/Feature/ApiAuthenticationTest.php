<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Modules\User\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\User\Enums\UserRole;
use Modules\User\Enums\UserStatus;
use Modules\User\Models\User;
use Modules\User\Notifications\PhoneVerificationCode;
use Modules\User\Notifications\VerifyEmail;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('a user can register through the versioned API', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'username' => 'new_user',
        'email' => 'new@example.com',
        'phone' => '+989121234567',
        'password' => 'Strong-password-123',
        'password_confirmation' => 'Strong-password-123',
        'device_name' => 'iPhone',
        'preferred_locale' => 'fa',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.username', 'new_user')
        ->assertJsonStructure(['token']);

    $user = User::query()->where('email', 'new@example.com')->firstOrFail();

    expect($user->hasRole(UserRole::User->value))->toBeTrue();
    Notification::assertSentTo($user, VerifyEmail::class);
    Notification::assertSentTo($user, PhoneVerificationCode::class);
});

test('email can be verified through a signed API link', function () {
    $user = User::factory()->unverified()->create();
    $url = URL::temporarySignedRoute('api.verification.verify', now()->addMinute(), [
        'id' => $user->getKey(),
        'hash' => sha1($user->getEmailForVerification()),
    ]);

    $this->getJson($url)->assertSuccessful();

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

test('phone can be verified with a one-time code', function () {
    Notification::fake();
    $user = User::factory()->create([
        'phone' => '+989121234567',
        'phone_verified_at' => null,
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/auth/phone/verification-code')->assertAccepted();

    $code = null;
    Notification::assertSentTo(
        $user,
        PhoneVerificationCode::class,
        function (PhoneVerificationCode $notification) use (&$code): bool {
            $code = $notification->code();

            return true;
        },
    );

    $this->postJson('/api/v1/auth/phone/verify', ['code' => $code])->assertSuccessful();

    expect($user->refresh()->phone_verified_at)->not->toBeNull();
});

test('a password can be reset through the API and existing tokens are revoked', function () {
    $user = User::factory()->create();
    $user->createToken('old-device');
    $token = Password::createToken($user);

    $this->postJson('/api/v1/auth/password/reset', [
        'email' => $user->email,
        'token' => $token,
        'password' => 'New-strong-password-123',
        'password_confirmation' => 'New-strong-password-123',
    ])->assertSuccessful();

    expect(Hash::check('New-strong-password-123', $user->refresh()->password))->toBeTrue()
        ->and($user->tokens()->count())->toBe(0);
});

test('a user can log in with email or phone', function (string $identifier) {
    $user = User::factory()->withVerifiedPhone()->create([
        'email' => 'member@example.com',
        'phone' => '+989121111111',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'identifier' => $identifier,
        'password' => 'password',
        'device_name' => 'Desktop',
    ])->assertSuccessful()->assertJsonStructure(['data', 'token']);

    expect($user->tokens()->count())->toBe(1);
})->with(['member@example.com', '+989121111111']);

test('a suspended user cannot log in', function () {
    User::factory()->create([
        'email' => 'suspended@example.com',
        'status' => UserStatus::Suspended,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'identifier' => 'suspended@example.com',
        'password' => 'password',
        'device_name' => 'Desktop',
    ])->assertUnprocessable();
});
