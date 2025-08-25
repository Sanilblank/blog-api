<?php

namespace App\Http\Requests\Users;

use App\Enums\Roles;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UserCreateRequest
 *
 * @package App\Http\Requests\Users
 */
class UserCreateRequest extends FormRequest
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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'string', 'in:'.implode(',', Roles::values())],
        ];
    }
}
