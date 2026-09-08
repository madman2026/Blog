<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Http\Requests\Api\V1\RegisterRequest;
use Modules\User\Actions\IssuePhoneVerificationChallenge;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;
use Modules\User\Transformers\UserResource;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __invoke(
        RegisterRequest $request,
        IssuePhoneVerificationChallenge $issuePhoneVerification,
    ): JsonResponse {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated): User {
            $user = User::query()->create([
                'username' => $validated['username'],
                'email' => mb_strtolower($validated['email']),
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'preferred_locale' => $validated['preferred_locale'] ?? config('platform.default_locale'),
            ]);

            $user->assignRole(UserRole::User->value);

            return $user;
        });

        $user->sendEmailVerificationNotification();
        $issuePhoneVerification->handle($user);

        return response()->json([
            'data' => new UserResource($user->load(['skills', 'media'])),
            'token' => $user->createToken($validated['device_name'])->plainTextToken,
        ], Response::HTTP_CREATED);
    }
}
