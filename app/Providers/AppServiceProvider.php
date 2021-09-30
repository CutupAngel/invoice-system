<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;
use Route;
use App;
use App\Http\Controllers\Controller;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::share('PackageClass', new \App\Package);

        View::share('server', gethostname());
        View::share('current', function ($page) {
            if (Route::current()->getPrefix() === $page) {
                return 'active';
            } elseif (url()->current() === $page) {
                return 'active';
            } elseif (strpos(url()->current(), $page) === 0) {
                return 'active';
            }

            return '';
        });

        View::share('site', function ($opt) {
            return Controller::site($opt);
        });

        App::bind('permissions', function () {
            return new \App\Packages\Permissions\Provider;
        });

        App::bind('settings', function () {
            return new \App\Packages\Settings\Provider;
        });

        App::bind('invoices', function () {
            return new \App\Packages\Invoices\Provider;
        });

        App::bind('integrations', function () {
            return new \App\Packages\Integrations\Provider;
        });

        App::bind('webhook', function () {
            return new \App\Packages\Webhooks\Provider;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
