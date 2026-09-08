<?php

namespace Modules\Blog\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\User\Enums\UserPermission;

class SaveCategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['sometimes', 'boolean'],
            'translations' => ['required', 'array', 'min:1', 'max:2'],
            'translations.*.locale' => ['required', 'string', Rule::in(config('platform.locales')), 'distinct'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255', 'distinct', 'regex:/^[\pL\pN]+(?:-[\pL\pN]+)*$/u'],
            'translations.*.description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(UserPermission::TaxonomiesManage->value) === true;
    }
}
