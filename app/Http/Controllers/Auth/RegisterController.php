<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class RegisterController extends Controller
{
    public function __construct(private RegistrationService $registration) {}

    public function create(): View
    {
        return view('register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $this->registration->register($request->validated());

        return redirect()->route('login')->with('status', 'User registered successfully, check your email');
    }
}
