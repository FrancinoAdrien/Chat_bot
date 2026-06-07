<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        return redirect()->route('chat.index')->with('error', 'Accès refusé. Vous devez être administrateur pour accéder à cette page.');
    }
}
