<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\TrackApiRequests::class);

        // Ajouter cette ligne pour désactiver CSRF sur les routes API
        $middleware->skipWhen(
            fn($request) => $request->is('api/*'),
            [\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]
        );

        // Vous pouvez également ajouter un alias si nécessaire
        $middleware->alias([
            'track.api.requests' => \App\Http\Middleware\TrackApiRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
