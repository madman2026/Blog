<?php

namespace Modules\Blog\Enums;

enum ReviewDecision: string
{
    case Publish = 'publish';
    case RequestChanges = 'request-changes';
    case Reject = 'reject';
}
