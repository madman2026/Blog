<?php

namespace Modules\Auth\Data;

final readonly class RegisterUserData
{
    public function __construct(
        public string $username,
        public string $email,
        public string $phone,
        public string $password,
        public string $preferredLocale,
    ) {}
}
