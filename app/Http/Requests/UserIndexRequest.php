<?php

namespace App\Http\Requests;

use App\Enums\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UserIndexRequest
 *
 * @package App\Http\Requests
 */
class UserIndexRequest extends FormRequest
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
     * If the request contains the 'roles' field, split it into an array of role names.
     * This allows passing multiple role names as a single string, separated by commas,
     * and still have the validation rules work correctly.
     */
    public function prepareForValidation(): void
    {
        if ($this->has('roles')) {
            $names = collect(explode(',', $this->input('roles')))
                ->map(fn($name) => empty($name) ? '' : $name)
                ->all();

            $this->merge([
                'roles' => $names,
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
            'roles'    => ['nullable', 'array'],
            'roles.*'  => ['nullable', 'string', Rule::in(Roles::values())]
        ];
    }
}
