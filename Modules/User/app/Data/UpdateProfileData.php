<?php

namespace Modules\User\Data;

final readonly class UpdateProfileData
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<string>|null  $skills
     */
    public function __construct(
        public array $attributes,
        public ?array $skills = null,
    ) {}
}
