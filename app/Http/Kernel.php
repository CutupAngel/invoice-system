<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \App\Http\Middleware\DatabaseSelector::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Impersonate::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \App\Http\Middleware\LogActivity::class,
        \GeneaLabs\LaravelCaffeine\Http\Middleware\LaravelCaffeineDripMiddleware::class,
        \App\Http\Middleware\SessionExpired::class,
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
          \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
          \App\Http\Middleware\EncryptCookies::class,
          \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
          \App\Http\Middleware\DatabaseSelector::class,
          \App\Http\Middleware\Impersonate::class,
          \App\Http\Middleware\VerifyCsrfToken::class,
          \App\Http\Middleware\LogActivity::class,
          \GeneaLabs\LaravelCaffeine\Http\Middleware\LaravelCaffeineDripMiddleware::class,
        ],

        'api' => [
          \App\Http\Middleware\Api::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'isSuperAdmin' => \App\Http\Middleware\isSuperAdmin::class,
        'isAdmin' => \App\Http\Middleware\isAdmin::class,
        'isClient' => \App\Http\Middleware\isClient::class,
        'isCustomer' => \App\Http\Middleware\isCustomer::class,
        'isStaff' => \App\Http\Middleware\isStaff::class,
        'api' => \App\Http\Middleware\Api::class,
        '2fa' => \PragmaRX\Google2FALaravel\Middleware::class,
        'checkout' => \App\Http\Middleware\checkout::class
    ];
}
