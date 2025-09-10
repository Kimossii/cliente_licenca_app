<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )->withMiddleware(function (Middleware $middleware): void {
        // Aqui fica a middleware de verificação de licença
        $middleware->alias([
            'license.check' => \App\Http\Middleware\LicenseCheck::class,
        ]);
        //Aqui se eu quiser aplicar globalmente
        //$middleware->prepend(\App\Http\Middleware\LicenseCheck::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

