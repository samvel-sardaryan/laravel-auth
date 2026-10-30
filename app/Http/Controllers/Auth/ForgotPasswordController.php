<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final readonly class ForgotPasswordController extends Controller
{
    public function __construct(private AccountService $account) {}

    public function create(): View
    {
        return view('forgot-password');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $this->account->sendResetLink($request->validated('email'));

        return back()->with('status', 'If an account exists for that email, we\'ve sent a reset link.');
    }
}
