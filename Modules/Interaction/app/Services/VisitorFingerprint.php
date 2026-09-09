<?php

namespace Modules\Interaction\Services;

final class VisitorFingerprint
{
    public function make(?int $userId, ?string $ipAddress, ?string $userAgent): string
    {
        $identity = $userId ?? implode('|', [$ipAddress, $userAgent]);

        return hash_hmac('sha256', (string) $identity, (string) config('app.key'));
    }
}
