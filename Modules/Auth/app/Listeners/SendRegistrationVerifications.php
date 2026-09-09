<?php

namespace Modules\Auth\Listeners;

use Modules\Auth\Events\UserRegistered;
use Modules\User\Actions\IssuePhoneVerificationChallenge;

final readonly class SendRegistrationVerifications
{
    public function __construct(private IssuePhoneVerificationChallenge $issuePhoneVerification) {}

    public function handle(UserRegistered $event): void
    {
        $event->user->sendEmailVerificationNotification();
        $this->issuePhoneVerification->handle($event->user);
    }
}
