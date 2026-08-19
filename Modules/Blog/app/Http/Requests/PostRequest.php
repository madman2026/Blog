<?php

namespace Modules\Blog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:20000',
            'body' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2000',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('web')->check()
            ?? auth('web')->user()->can('posts.create');
    }
}
