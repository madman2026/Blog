<?php

namespace Modules\Blog\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending-review';
    case ChangesRequested = 'changes-requested';
    case Published = 'published';
    case Archived = 'archived';
}
