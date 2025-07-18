<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController
{
    public function autoLogin(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->user);
        Auth::login($user);

        return redirect()->intended('/dashboard'); // or wherever you want
    }
}
