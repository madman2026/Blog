<?php

namespace Modules\Interaction\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;

class ModerateCommentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(CommentStatus::class)->only([
                    CommentStatus::Approved,
                    CommentStatus::Rejected,
                ]),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $comment = $this->route('comment');

        return $comment instanceof Comment
            && $this->user()?->can('moderate', $comment) === true;
    }
}
