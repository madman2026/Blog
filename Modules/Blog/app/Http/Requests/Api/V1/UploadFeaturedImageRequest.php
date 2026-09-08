<?php

namespace Modules\Blog\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Blog\Models\Post;

class UploadFeaturedImageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
                'dimensions:min_width=640,min_height=360,max_width=6000,max_height=6000',
            ],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('managedPost');

        return $post instanceof Post && $this->user()?->can('update', $post) === true;
    }
}
