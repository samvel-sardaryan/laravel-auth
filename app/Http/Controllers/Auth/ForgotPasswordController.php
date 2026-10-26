<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

final readonly class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If an account exists for that email, we\'ve sent a reset link.');
    }
}
