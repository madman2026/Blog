<?php

namespace Modules\User\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Modules\User\Models\User;

class ConfirmPhoneVerificationChallenge
{
    public function __construct(private readonly IssuePhoneVerificationChallenge $issuer) {}

    public function handle(User $user, string $code): void
    {
        $key = $this->issuer->key($user);
        $challenge = Cache::get($key);

        if (! is_array($challenge) || ! isset($challenge['digest'], $challenge['attempts'])) {
            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid or expired.'),
            ]);
        }

        if ((int) $challenge['attempts'] >= 5) {
            Cache::forget($key);

            throw ValidationException::withMessages([
                'code' => __('Too many verification attempts. Request a new code.'),
            ]);
        }

        $digest = hash_hmac('sha256', $code, (string) config('app.key'));

        if (! hash_equals((string) $challenge['digest'], $digest)) {
            $challenge['attempts'] = (int) $challenge['attempts'] + 1;
            Cache::put($key, $challenge, now()->addMinutes(10));

            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid or expired.'),
            ]);
        }

        $user->forceFill(['phone_verified_at' => now()])->save();
        Cache::forget($key);
    }
}
