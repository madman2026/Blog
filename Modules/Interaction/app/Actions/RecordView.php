<?php

namespace Modules\Interaction\Actions;

use Illuminate\Database\Eloquent\Model;
use Modules\Interaction\Interfaces\Repositories\EngagementRepository;
use Modules\Interaction\Models\View;
use Modules\Interaction\Services\VisitorFingerprint;
use Modules\User\Models\User;

final readonly class RecordView
{
    public function __construct(
        private VisitorFingerprint $fingerprint,
        private EngagementRepository $engagements,
    ) {}

    public function handle(
        Model $viewable,
        ?User $user,
        ?string $ipAddress,
        ?string $userAgent,
    ): View {
        return $this->engagements->recordView(
            $viewable,
            $this->fingerprint->make($user?->getKey(), $ipAddress, $userAgent),
            $user?->getKey(),
        );
    }
}
