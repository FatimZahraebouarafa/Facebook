<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // ...existing code...
        \App\Http\Middleware\LogErrors::class,
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // ...existing code...
    ];

    /**
     * The application's middleware groups.
     *
     * These middleware may be assigned to routes when they are registered.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // ...existing code...
            \App\Http\Middleware\LogAuthErrors::class,
        ],
        
        // ...existing code...
    ];
}