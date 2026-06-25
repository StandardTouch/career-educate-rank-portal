<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureSingleDeviceSession;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin/*')
            ? route('admin.login')
            : route('login'));

        $middleware->validateCsrfTokens(except: [
            'exotel/voice-analyze-webhook',
        ]);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'paid' => \App\Http\Middleware\RequirePaidPlan::class,
            'single.device' => EnsureSingleDeviceSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

$app->usePublicPath(dirname(__DIR__, 2));

return $app;
