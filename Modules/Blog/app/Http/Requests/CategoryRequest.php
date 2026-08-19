<?php

namespace Modules\Blog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:categories,name',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return
            auth('web')->check() ??
            auth('web')
                ->user()
                ->can('categories.create');
    }
}
