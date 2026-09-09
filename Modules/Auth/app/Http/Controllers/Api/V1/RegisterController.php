<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Actions\RegisterUser;
use Modules\Auth\Data\RegisterUserData;
use Modules\Auth\Http\Requests\Api\V1\RegisterRequest;
use Modules\User\Transformers\UserResource;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __invoke(
        RegisterRequest $request,
        RegisterUser $register,
    ): JsonResponse {
        $validated = $request->validated();

        $user = $register->handle(new RegisterUserData(
            username: $validated['username'],
            email: $validated['email'],
            phone: $validated['phone'],
            password: $validated['password'],
            preferredLocale: $validated['preferred_locale'] ?? config('platform.default_locale'),
        ));

        return response()->json([
            'data' => new UserResource($user->load(['skills', 'media'])),
            'token' => $user->createToken($validated['device_name'])->plainTextToken,
        ], Response::HTTP_CREATED);
    }
}
