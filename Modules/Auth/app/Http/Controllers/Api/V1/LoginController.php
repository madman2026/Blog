<?php

namespace Modules\Auth\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Http\Requests\Api\V1\LoginRequest;
use Modules\User\Enums\UserStatus;
use Modules\User\Models\User;
use Modules\User\Transformers\UserResource;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $identifier = mb_strtolower($validated['identifier']);
        $field = str_starts_with($identifier, '+') ? 'phone' : 'email';

        $user = User::query()->where($field, $identifier)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => __('auth.failed'),
            ]);
        }

        if ($user->status === UserStatus::Suspended) {
            throw ValidationException::withMessages([
                'identifier' => __('This account is suspended.'),
            ]);
        }

        return response()->json([
            'data' => new UserResource($user->load(['skills', 'media'])),
            'token' => $user->createToken($validated['device_name'])->plainTextToken,
        ]);
    }
}
