<?php

namespace Modules\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\User\Models\User;

class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => null,
            'phone_verified_at' => null,
            'avatar_path' => null,
            'bio' => fake()->optional()->text(160),
            'about' => fake()->optional()->paragraph(),
            'social_links' => null,
            'preferred_locale' => 'fa',
            'status' => 'active',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withVerifiedPhone(): static
    {
        return $this->state(fn (array $attributes): array => [
            'phone' => fake()->unique()->e164PhoneNumber(),
            'phone_verified_at' => now(),
        ]);
    }
}
