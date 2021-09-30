<?php

namespace App\Providers;

use Storage;

use App\Order_Group;
use App\Package;
use App\Package_File;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
    ];

    protected $subscribe = [
        \App\Listeners\LogLogins::class,
        \App\Listeners\SubscriptionEnforment::class
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Package_File::deleting(function ($file) {
            try {
                return Storage::disk('minio')->delete($file->path);
            } catch (\Exception $e) {
                return true;
            }
        });

        Package::deleting(function ($package) {
            foreach ($package->files as $file) {
                $file->delete();
            }
        });

        Order_Group::deleting(function ($group) {
            foreach ($group->packages as $package) {
                $package->delete();
            }
        });

        \App\User::deleting(function($user) {
            $user->orders()->delete();
            $user->invoices()->delete();
        });
    }
}
