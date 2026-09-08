<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\Api\V1\VerifyPhoneRequest;
use Modules\User\Actions\ConfirmPhoneVerificationChallenge;
use Modules\User\Actions\IssuePhoneVerificationChallenge;
use Symfony\Component\HttpFoundation\Response;

class PhoneVerificationController extends Controller
{
    public function send(Request $request, IssuePhoneVerificationChallenge $issue): JsonResponse
    {
        abort_if(blank($request->user()->phone), 422, __('Add a phone number before verification.'));

        if ($request->user()->phone_verified_at === null) {
            $issue->handle($request->user());
        }

        return response()->json([], Response::HTTP_ACCEPTED);
    }

    public function verify(
        VerifyPhoneRequest $request,
        ConfirmPhoneVerificationChallenge $confirm,
    ): JsonResponse {
        $confirm->handle($request->user(), $request->validated('code'));

        return response()->json(['message' => __('Phone number verified successfully.')]);
    }
}
