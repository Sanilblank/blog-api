<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CategoryCreateRequest
 *
 * @package App\Http\Requests
 */
class CategoryCreateRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (Category::where('name', 'ilike', $value)->exists()) {
                        $fail(__('validation.unique', ['attribute' => 'name']));
                    }
                }
            ],
        ];
    }
}
