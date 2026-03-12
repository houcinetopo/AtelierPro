<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Log les actions importantes des utilisateurs
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ne logger que les actions POST/PUT/PATCH/DELETE
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && auth()->check()) {
            $action = match($request->method()) {
                'POST' => 'create',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'delete',
                default => 'unknown',
            };

            // Extraire le nom du module depuis l'URL
            $segments = $request->segments();
            $module = $segments[0] ?? 'unknown';

            ActivityLog::log(
                action: $action,
                description: ucfirst($action) . " dans le module '{$module}'"
            );
        }

        return $response;
    }
}
