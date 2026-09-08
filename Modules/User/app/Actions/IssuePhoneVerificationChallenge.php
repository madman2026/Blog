<?php

namespace Modules\User\Actions;

use Illuminate\Support\Facades\Cache;
use Modules\User\Models\User;
use Modules\User\Notifications\PhoneVerificationCode;

class IssuePhoneVerificationChallenge
{
    public function handle(User $user): void
    {
        $code = (string) random_int(100_000, 999_999);

        Cache::put($this->key($user), [
            'digest' => hash_hmac('sha256', $code, (string) config('app.key')),
            'attempts' => 0,
        ], now()->addMinutes(10));

        $user->notify(new PhoneVerificationCode($code));
    }

    public function key(User $user): string
    {
        return 'phone-verification:'.$user->getKey().':'.hash('sha256', (string) $user->phone);
    }
}
