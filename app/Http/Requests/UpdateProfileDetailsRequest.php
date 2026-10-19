<?php

namespace App\Http\Requests;

use App\Models\Profile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileDetailsRequest extends FormRequest
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
            'first_name' => ['nullable', 'string', 'max:' . Profile::NAME_MAX_LENGTH],
            'last_name' => ['nullable', 'string', 'max:' . Profile::NAME_MAX_LENGTH],
            'phone' => ['nullable', 'string', 'max:' . Profile::PHONE_MAX_LENGTH, 'regex:/^\+?[0-9 ()\-]+$/', function (string $attribute, mixed $value, Closure $fail) {
                if (strlen(preg_replace('/\D/', '', $value)) < Profile::PHONE_MIN_DIGITS || strlen(preg_replace('/\D/', '', $value)) > Profile::PHONE_MAX_DIGITS) {
                    $fail('Phone must contain between ' . Profile::PHONE_MIN_DIGITS . ' and ' . Profile::PHONE_MAX_DIGITS . ' digits.');
                }
            }],
            'location' => ['nullable', 'string', 'max:' . Profile::LOCATION_MAX_LENGTH],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'bio' => ['nullable', 'string', 'max:' . Profile::BIO_MAX_LENGTH],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . Profile::AVATAR_MAX_KILOBYTES],
        ];
    }
}
