<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogAuthErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Vérifier si c'est une route d'authentification
            $authRoutes = ['login', 'register', 'logout', 'password.reset', 'password.email'];
            $isAuthRoute = false;
            
            foreach ($authRoutes as $route) {
                if (strpos($request->path(), $route) !== false) {
                    $isAuthRoute = true;
                    break;
                }
            }
            
            if ($isAuthRoute) {
                Log::info('Accès à une route d\'authentification: ' . $request->path(), [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'method' => $request->method()
                ]);
            }
            
            return $next($request);
        } catch (Throwable $e) {
            // Enregistrer l'erreur
            Log::error('Exception non gérée dans LogAuthErrors middleware: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'route' => $request->path(),
                'method' => $request->method()
            ]);
            
            // Retourner une réponse d'erreur conviviale
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Une erreur est survenue lors de l\'authentification.'], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['email' => 'Une erreur système est survenue. Veuillez réessayer ultérieurement.']);
        }
    }
}
