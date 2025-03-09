<?php

namespace App\Http\Middleware;

use App\Models\ApiRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Vérifier si la requête vient bien de l'API et non de Livewire
        if (Auth::check() && !$this->shouldSkip($request)) {
            ApiRequest::create([
                'user_id' => Auth::id(),
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'payload' => $request->except(['password', 'password_confirmation']),
                'response_status' => $response->getStatusCode(),
            ]);
        }

        return $response;
    }

    /**
     * Détermine si la requête doit être ignorée pour le tracking.
     */
    private function shouldSkip(Request $request): bool
    {
        // Ignorer les requêtes Livewire
        if (str_contains($request->path(), 'livewire')) {
            return true;
        }

        // Ignorer les requêtes du panneau admin
        if (str_starts_with($request->path(), 'admin')) {
            return true;
        }

        // Vous pouvez ajouter d'autres conditions selon vos besoins

        return false;
    }
}
