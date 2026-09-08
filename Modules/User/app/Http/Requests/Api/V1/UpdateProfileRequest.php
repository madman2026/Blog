<?php

namespace Modules\User\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => ['sometimes', 'string', 'min:3', 'max:32', 'alpha_dash:ascii', 'unique:users,username,'.$this->user()?->getKey()],
            'bio' => ['sometimes', 'nullable', 'string', 'max:280'],
            'about' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'preferred_locale' => ['sometimes', 'string', 'in:fa,en'],
            'social_links' => ['sometimes', 'array', 'max:10'],
            'social_links.*' => ['nullable', 'url:http,https', 'max:500'],
            'skills' => ['sometimes', 'array', 'max:20'],
            'skills.*' => ['required', 'string', 'max:80', 'distinct'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
