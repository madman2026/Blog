<?php

namespace Modules\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;

class AuthorApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = AuthorApplication::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => AuthorApplicationStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ];
    }
}
