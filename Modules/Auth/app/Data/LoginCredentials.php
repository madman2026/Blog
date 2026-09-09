<?php

namespace Modules\Auth\Data;

final readonly class LoginCredentials
{
    public function __construct(
        public string $identifier,
        public string $password,
    ) {}
}
