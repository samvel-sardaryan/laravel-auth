<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CategoryRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:'.Category::NAME_MAX_LENGTH, Rule::unique('categories')->ignore($this->route('category'))],
            'slug' => ['required', 'string', 'max:'.Category::NAME_MAX_LENGTH, 'regex:/^[a-z0-9-]+$/', Rule::unique('categories')->ignore($this->route('category'))],
        ];
    }
}
