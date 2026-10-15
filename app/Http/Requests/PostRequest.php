<?php

namespace App\Http\Requests;

use App\Enums\PostStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $post = $this->route('post');
                $existing = $post ? $post->images()->count() : 0;
                $removing = $post ? $post->images()->whereIn('id', $this->input('remove_images', []))->count() : 0;
                $adding = count($this->file('images', []));
                if ($existing - $removing + $adding > 5) {
                    $validator->errors()->add('images', 'You cannot add more than 5 images.');
                }
            },
        ];
    }
}
