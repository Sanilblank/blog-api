<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class PostCreateRequest
 *
 * @package App\Http\Requests\Post
 */
class PostCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags'        => ['required', 'array'],
            'tags.*'      => ['integer', 'exists:tags,id'],
        ];
    }

    /**
     * Custom message for validation.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'tags.*.integer' => __('validation.integer', ['attribute' => 'tags']),
            'tags.*.exists'  => __('validation.exists', ['attribute' => 'tags']),
        ];
    }
}
