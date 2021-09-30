<?php

namespace App\Providers;

use View;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    private static $notifications = [];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('Common.notifications', function ($view) {
            foreach (glob(app_path("Modules/*/notifications.php")) as $file) {
                include($file);
            }

            $view->with('notifications', \App\Providers\NotificationServiceProvider::class);
        });
    }

    public static function count()
    {
        return count(self::$notifications);
    }

    public static function get()
    {
        return self::$notifications;
    }

    public static function add($string, $link, $icon)
    {
        self::$notifications[] = [
            'string' => $string,
            'link' => $link,
            'icon' => $icon
        ];
    }

    public static function label()
    {
        $label = 'success';
        if (self::count() < 5 && self::count() >= 9) {
            $label = 'warning';
        } elseif (self::count() > 10) {
            $label = 'danger';
        }

        return $label;
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
