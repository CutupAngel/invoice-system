<?php

namespace App\Listeners;

use Auth;
use Request;

use App\Login_History;
use App\Modules\Controller;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogLogins
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function onUserLogin(Login $event)
    {
        $attempt = Login_History::where('username', $event->user->username)
            ->whereNull('logout')
            ->orderBy('created_at', 'desc')->first();

        if (is_null($attempt)) {
            $attempt = $this->createRecord($event->user->username);
        }

        $attempt->failed = false;
        $attempt->save();


    }

    public function onLoginAttempt(Attempting $event)
    {
        $this->createRecord($event->credentials['username']);
    }

    public function onUserLogout(Logout $event)
    {
        $login = Login_History::where('username', $event->user->username)
            ->whereNull('logout')
            ->orderBy('created_at', 'desc')->first();

        $login->logout = date('Y-m-d h:m:S');
        $login->save();
    }

    private function createRecord($username)
    {
        $attempt = new Login_History;
        $attempt->username = $username;
        $attempt->ip = Request::getClientIp();
        $attempt->failed = true;
        $attempt->logout = $attempt->created_at;
        $attempt->save();

        return $attempt;
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            Attempting::class,
            'App\Listeners\LogLogins@onLoginAttempt'
        );

        $events->listen(
            Login::class,
            'App\Listeners\LogLogins@onUserLogin'
        );

        $events->listen(
            Logout::class,
            'App\Listeners\LogLogins@onUserLogout'
        );
    }
}
