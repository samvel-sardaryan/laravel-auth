<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        User::create($validated);

        return redirect()->route('login')->with('status', 'User registered successfully');
    }
}
