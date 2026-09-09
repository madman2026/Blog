<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Actions\AuthenticateUser;
use Modules\Auth\Data\LoginCredentials;
use Modules\Auth\Http\Requests\Api\V1\LoginRequest;
use Modules\User\Transformers\UserResource;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, AuthenticateUser $authenticate): JsonResponse
    {
        $validated = $request->validated();
        $user = $authenticate->handle(new LoginCredentials(
            identifier: $validated['identifier'],
            password: $validated['password'],
        ));

        return response()->json([
            'data' => new UserResource($user->load(['skills', 'media'])),
            'token' => $user->createToken($validated['device_name'])->plainTextToken,
        ]);
    }
}
