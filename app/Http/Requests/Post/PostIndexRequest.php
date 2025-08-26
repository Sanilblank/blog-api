<?php

namespace App\Http\Requests\Post;

use App\Enums\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class PostIndexRequest
 *
 * @package App\Http\Requests\Post
 */
class PostIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the request for validation.
     *
     * If the request contains the 'tags' field, split it into an array of tag IDs.
     * This allows passing multiple tag IDs as a single string, separated by commas,
     * and still have the validation rules work correctly.
     */
    public function prepareForValidation(): void
    {
        if ($this->has('tags')) {
            $ids = collect(explode(',', $this->input('tags')))
                ->map(fn($id) => empty($id) ? '' : $id)
                ->all();

            $this->merge([
                'tags' => $ids,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'search'   => ['nullable', 'string'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'tags'     => ['nullable', 'array'],
            'tags.*'   => ['nullable', 'string', 'exists:tags,id'],
            'author'   => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'tags.*.exists' => __('validation.exists', ['attribute' => 'tag']),
            'tags.*.string' => __('validation.string', ['attribute' => 'tag']),
        ];
    }
}
