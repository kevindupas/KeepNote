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

        // Spécifier les groupes de middleware pour les routes web et api
        $middleware->web([
            // Gardez VerifyCsrfToken ici pour les routes web
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->api([
            // Ne pas inclure VerifyCsrfToken dans le groupe api
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // autres middleware api selon besoin
        ]);

        // Vous pouvez également ajouter un alias si nécessaire
        $middleware->alias([
            'track.api.requests' => \App\Http\Middleware\TrackApiRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
