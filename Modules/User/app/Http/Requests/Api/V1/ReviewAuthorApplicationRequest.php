<?php

namespace Modules\User\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Models\AuthorApplication;

class ReviewAuthorApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(AuthorApplicationStatus::class)
                    ->only([AuthorApplicationStatus::Approved, AuthorApplicationStatus::Rejected]),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $application = $this->route('authorApplication');

        return $application instanceof AuthorApplication
            && $this->user()?->can('review', $application) === true;
    }
}
