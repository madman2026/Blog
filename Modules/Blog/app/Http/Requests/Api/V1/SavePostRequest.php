<?php

namespace Modules\Blog\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Models\Post;

class SavePostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PostType::class)],
            'translations' => ['required', 'array', 'min:1', 'max:2'],
            'translations.*.locale' => ['required', 'string', Rule::in(config('platform.locales')), 'distinct'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.summary' => ['nullable', 'string', 'max:1000'],
            'translations.*.body' => ['required', 'string', 'max:200000'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:70'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:320'],
            'category_ids' => ['sometimes', 'array', 'max:5'],
            'category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            'tag_ids' => ['sometimes', 'array', 'max:20'],
            'tag_ids.*' => ['integer', 'distinct', 'exists:tags,id'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('managedPost');

        return $post instanceof Post
            ? $this->user()?->can('update', $post) === true
            : $this->user()?->can('create', Post::class) === true;
    }
}
