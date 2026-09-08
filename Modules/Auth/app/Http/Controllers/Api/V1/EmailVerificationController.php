<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\User\Models\User;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return response()->json([], Response::HTTP_ACCEPTED);
    }

    public function verify(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        abort_unless(hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => __('Email verified successfully.')]);
    }
}
