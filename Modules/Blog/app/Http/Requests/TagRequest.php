<?php

namespace Modules\Blog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('web')->check()
            ?? auth('web')->user()->can('tags.create');
    }
}
