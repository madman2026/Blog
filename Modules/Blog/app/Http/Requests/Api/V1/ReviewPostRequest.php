<?php

namespace Modules\Blog\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Blog\Enums\ReviewDecision;
use Modules\Blog\Models\Post;

class ReviewPostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(ReviewDecision::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('managedPost');

        return $post instanceof Post
            && $this->user()?->can('review', $post) === true;
    }
}
