<?php

namespace Modules\User\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\User\Enums\UserPermission;
use Modules\User\Enums\UserStatus;

class UpdateUserStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(UserStatus::class)],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(UserPermission::UsersSuspend->value) === true;
    }
}
