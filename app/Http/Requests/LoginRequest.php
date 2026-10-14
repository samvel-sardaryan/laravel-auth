<?php

namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email').'|'.$this->ip()));
    }

    public function authenticate(): void
    {
        $key = $this->throttleKey();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            event(new Lockout($this));
            throw ValidationException::withMessages(['email' => 'Too many login attempts. Try again in '.ceil(RateLimiter::availableIn($key) / 60).' minutes.']);
        }
        if (! Auth::attempt($this->validated())) {
            RateLimiter::hit($key, 15 * 60);
            throw ValidationException::withMessages(['email' => 'Invalid email or password.']);
        }
        RateLimiter::clear($key);
    }
}
