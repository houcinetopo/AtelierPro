<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Vérifie si l'utilisateur a le rôle requis
     *
     * Usage dans les routes :
     *   ->middleware('role:admin')
     *   ->middleware('role:admin,gestionnaire')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Vérifier si l'utilisateur est actif
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte a été désactivé. Contactez l\'administrateur.']);
        }

        // L'admin a toujours accès
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a un des rôles requis
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Accès non autorisé. Vous n\'avez pas les permissions nécessaires.');
        }

        return $next($request);
    }
}
