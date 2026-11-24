<?php

use App\Http\Middleware\VerifyCustomerToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'utility-account.access' => VerifyCustomerToken::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'utility-accounts',
            'utility-accounts/*',
            'service-providers',
            'service-providers/*',
            'utility-services/*',
            'bills/*',
            'payments/*',
            'users',
            'users/login',
            'users/*/block',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request): bool => $request->expectsJson() || ! $request->is('/'));
    })->create();
