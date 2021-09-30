<?php

namespace App\Listeners;

use Auth;
use App\Exceptions\SubscriptionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionEnforment
{
    private static $limits;
    private static $bypassSubscriptionCheck = [
        'api/migrate'
    ];

    private function getLimits($limit)
    {
        if (empty(self::$limits)) {
            $site = Config('app.site');

            if ($site->status !== '2') {
                echo View('Common.subscriptionExpired');
                die;
            }

            self::$limits = [
                'invoice' => $site->activeSubscription ? $site->activeSubscription->plan->invoices : null,
                'clients' => $site->activeSubscription ? $site->activeSubscription->plan->clients : null,
                'staff' => $site->activeSubscription ? $site->activeSubscription->plan->staff : null,
                'status' => $site->status === '2'
            ];
        }

        return self::$limits[$limit];
    }

    public function subscriptionCheck(\Illuminate\Routing\Events\RouteMatched $event)
    {
        if (in_array($event->route->Uri(), self::$bypassSubscriptionCheck)) {
            return true;
        }

        if (! Auth::guest() && ! $this->getLimits('status') && ! Auth::User()->isCustomer()) {
            echo View('Common.subscriptionExpired');
            die;
        }
    }

    public function invoiceCheck(\App\Invoice $invoice)
    {
        if (! Auth::guest() && $this->getLimits('invoice') !== 0 && ! Auth::User()->isCustomer()) {
            if ($this->getLimits('invoice') < (\App\Invoice::count() + 1)) {
                throw new SubscriptionException('Over invoice limit', SubscriptionException::INVOICELIMIT);
            }
        }
    }

    public function customerCheck(\App\User $user)
    {
        if (! Auth::guest() && $this->getLimits('clients') !== 0 && ! Auth::User()->isCustomer()) {
            if ($this->getLimits('clients') < (\App\User::where('account_type', \App\User::CUSTOMER)->count() + 1)) {
                throw new SubscriptionException('Over client limit', SubscriptionException::CLIENTLIMIT);
            }
        }
    }

    public function staffCheck(\App\User $user)
    {
        if (! Auth::guest() && $this->getLimits('staff') !== 0 && ! Auth::User()->isCustomer()) {
            if ($this->getLimits('staff') < (\App\User::where('account_type', \App\User::STAFF)->count() + 1)) {
                throw new SubscriptionException('Over staff limit', SubscriptionException::STAFFLIMIT);
            }
        }
    }

    public function userCheck(\App\User $user)
    {
        switch ($user->account_type)
        {
            case $user::STAFF:
                return $this->staffCheck($user);
                break;
            case $user::CUSTOMER:
                return $this->customerCheck($user);
                break;
            default:
                return true;
        }
    }

    public function subscribe($events)
    {
        $events->listen('eloquent.creating: App\Invoice', [$this, 'invoiceCheck']);
        $events->listen('eloquent.creating: App\User', [$this, 'userCheck']);
        $events->listen('Illuminate\Routing\Events\RouteMatched', [$this, 'subscriptionCheck']);
    }
}
