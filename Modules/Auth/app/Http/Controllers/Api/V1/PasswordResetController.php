<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Actions\CompletePasswordReset;
use Modules\Auth\Http\Requests\Api\V1\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\Api\V1\ResetPasswordRequest;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->safe()->only('email'));

        return response()->json([
            'message' => __('If an account exists, a password reset link has been sent.'),
        ], Response::HTTP_ACCEPTED);
    }

    public function reset(ResetPasswordRequest $request, CompletePasswordReset $completePasswordReset): JsonResponse
    {
        $completePasswordReset->handle(
            $request->safe()->only('email', 'password', 'password_confirmation', 'token'),
        );

        return response()->json(['message' => __(Password::PasswordReset)]);
    }
}
