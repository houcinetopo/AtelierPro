<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        // Rate limiting : 5 tentatives max par minute
        $throttleKey = Str::transliterate(Str::lower($request->email) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'Ces identifiants ne correspondent à aucun compte.',
            ]);
        }

        // Vérifier si le compte est actif
        if (!Auth::user()->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        // Mettre à jour last_login_at
        Auth::user()->update(['last_login_at' => now()]);

        // Logger la connexion
        ActivityLog::log('login', 'Connexion au système');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        ActivityLog::log('logout', 'Déconnexion du système');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
