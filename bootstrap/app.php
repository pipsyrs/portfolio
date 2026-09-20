<?php

use App\Http\Middleware\EnforceAbsoluteSessionLifetime;
use App\Http\Middleware\EnforceMaintenanceMode;
use App\Http\Middleware\EnsureIsOwner;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
            EnforceMaintenanceMode::class,
        ]);

        $middleware->alias([
            'owner' => EnsureIsOwner::class,
            'auth.session' => AuthenticateSession::class,
            'session.absolute' => EnforceAbsoluteSessionLifetime::class,
            'track.visitor' => TrackVisitor::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('dashboard.login'));
        $middleware->redirectUsersTo(fn () => route('dashboard.home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
