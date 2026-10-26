<?php

namespace App\Http\Requests;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PostRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:'.Post::TITLE_MAX_LENGTH],
            'content' => ['required', 'string', 'max:'.Post::CONTENT_MAX_LENGTH],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:'.Post::TAGS_INPUT_MAX_LENGTH],
            'images' => ['nullable', 'array', 'max:'.Post::MAX_IMAGES],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:'.PostImage::MAX_KILOBYTES],
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
                if ($existing - $removing + $adding > Post::MAX_IMAGES) {
                    $validator->errors()->add('images', 'You cannot add more than '.Post::MAX_IMAGES.' images.');
                }
            },
        ];
    }
}
