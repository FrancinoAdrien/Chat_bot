<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle the login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'matricule' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['matricule' => $credentials['matricule'], 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('chat.index'));
        }

        return back()->withErrors([
            'matricule' => 'Les identifiants fournis ne correspondent pas à nos enregistrements ou votre compte est inactif.',
        ])->onlyInput('matricule');
    }

    /**
     * Handle the logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
