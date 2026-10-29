<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

final readonly class VerifyEmailController extends Controller
{
    public function __construct(private RegistrationService $registration) {}

    public function notice(Request $request): View|RedirectResponse
    {
        return ! $request->user() || $request->user()->hasVerifiedEmail() ? redirect()->route('dashboard') : view('verify-email');
    }

    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = $this->registration->findForVerification((int) $id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(Response::HTTP_FORBIDDEN, 'This link is invalid');
        } elseif ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('status', 'You are already verified');
        }

        $this->registration->verifyEmail($user);

        if ($request->user()) {
            return redirect()->route('dashboard')->with('status', 'Email verified successfully');
        } else {
            return redirect()->route('login')->with('status', 'Email verified successfully, please login');
        }
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('status', 'You are already verified');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent');
    }
}
