<?php

namespace App\Console\Commands\Crons;

use Auth;
use Invoices;
use Settings;

use App\Invoice;
use App\InvoiceItem;
use App\User;
use App\Subscription;
use App\Plan;
use App\Plan_Cycle;

use Illuminate\Console\Command;

class BillingservSubscriptions extends Command
{
    protected $name = 'BS:cron:subscriptions';
    protected $description = 'Generate Invoices and Send Emails for BillingServ Subscriptions.';

    public function handle()
    {
        $dates = [
            2 => strtotime('+1 day'),
            3 => strtotime('+1 week'),
            4 => strtotime('+2 weeks'),
            5 => strtotime('+1 month'),
            6 => strtotime('+2 months'),
            7 => strtotime('+3 months'),
            8 => strtotime('+4 months'),
            9 => strtotime('+5 months'),
            10 => strtotime('+6 months'),
            11 => strtotime('+7 months'),
            12 => strtotime('+8 months'),
            13 => strtotime('+9 months'),
            14 => strtotime('+10 months'),
            15 => strtotime('+11 months'),
            16 => strtotime('+1 year'),
            17 => strtotime('+2 years'),
            18 => strtotime('+3 years')
        ];

        $usersWithSubscriptions = User::whereHas('billingserv_subscriptions', function ($q) {
            $q->where('status', Subscription::SETUP);
            $q->whereHas('cycle', function ($q) {
                $q->where('cycle', '!=', '1');
            });
        })->get()
            ->load('billingserv_subscriptions')
            ->load('billingserv_subscriptions.cycle');

        $this->info("Running BillingServ Subscriptions " . date('Y-m-d h:m:s'));
        foreach ($usersWithSubscriptions as $user) {
            $this->info("Checking active subscriptions for user {$user->id}");
            $subscriptions = $user->billingserv_subscriptions->groupBy('cycle.cycle');

            // This is needed to mail the invoice info.
            $_SERVER['HTTP_HOST'] = $user->getSetting('site.url');

            foreach ($dates as $i => $date) {
                $dueDate = date('Y-m-d h:m:s', $date);
                $date = strtotime('-' . $user->getSetting('invoice.days2send', 0) . ' days', $date);

                if (isset($subscriptions[$i])) {
                    $invoices = $subscriptions[$i]->filter(function($item) use ($date) {
                        return strtotime($item->last_invoice) <= $date;
                    });

                    foreach ($invoices as $invoice) {
                        Invoices::create(1, $invoice->user_id, $dueDate);
                        Invoices::addItem($invoice->plan->name, '', '', $invoice->cycle->price, 1);

                        $invoice->last_invoice = $dueDate;

                        if (!empty($invoice->newplan) && $invoice->newplan !== 0) {
                            $invoice->status = Subscription::CANCELLED;

                            $newplan = Plan::where('id', $invoice->newplan)->first();
                            $newplanCycle = Plan_Cycle::where('plan_id', $invoice->newplan)->first();

                            $subscription = new Subscription;
                            $subscription->user_id = $invoice->user_id,;
                            $subscription->plan_id = $invoice->newplan;
                            $subscription->cycle_id = $newplanCycle->id;
                            $subscription->status = Subscription::SETUP;
                            $subscription->last_invoice = $dueDate;
                            $subscription->save();

                            Invoices::addItem($newplan->name, '', '', $newplanCycle->price, 1);
                        }

                        Invoices::save();
                        Invoices::sendEmail();

                        $invoice->save();
                    }

                    $this->line(count($invoices) . ' for ' . Plan_Cycle::$cycles[$i]);
                } else {
                    $this->line('0 for ' . Plan_Cycle::$cycles[$i]);
                }
            }

            $this->comment(count($subscriptions) . ' total checked');

            $this->line('');
        }
    }
}
