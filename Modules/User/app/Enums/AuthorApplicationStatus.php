<?php

namespace Modules\User\Enums;

enum AuthorApplicationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
