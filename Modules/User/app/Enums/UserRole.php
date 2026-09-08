<?php

namespace Modules\User\Enums;

enum UserRole: string
{
    case SuperUser = 'super-user';
    case Admin = 'admin';
    case Author = 'author';
    case User = 'user';
}
