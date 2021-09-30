<?php

namespace App\Console\Commands\Crons;


use App\InvoiceItem;
use Illuminate\Support\Facades\Log;
use Invoices;
use Settings;

use App\Address;
use App\Currency;
use App\Countries;
use App\Site;
use App\Invoice;
use App\Package_Cycle;
use App\Order;
use App\Order_Options;
use App\User;
use App\Http\Controllers\PaymentController;
use App\Mail\InvoiceEmail;
use App\Integrations\DirectAdmin;
use App\Integrations\CPanel;
use App\Integration;
use App\IntegrationCpanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use PH7\Eu\Vat\Validator as VatValidator;
use PH7\Eu\Vat\Provider\Europa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use PDOException;

class ReoccuringOrders extends Command
{
    /**
     * @var string
     */
    protected $signature = 'BS:cron:reoccuringOrders {--server=}';

    /**
     * @var string
     */
    protected $description = 'Generate Invoices and Send Emails for Reoccuring Orders.';

    /**
     * ReoccuringOrders constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
    {
        $this->info("Running Reoccuring Orders " . now()->toDateTimeString());

        $sites = Site::all();

        foreach ($sites as $k => $site) {

            if ($site->server_region_id == $this->option('server')) {

                $this->info("Site:" . $site->domain);

                try {
                    $site->setDatabase();

                    DB::reconnect('site');
                    DB::setDefaultConnection('site');
                } catch (PDOException $e) {
                    $this->line('Connection failed on: ' . $site->domain);
                    continue;
                }

                //remove trial for expired
                $this->removeTrialWhenExpired();

                $dates = [
                    2  => strtotime('-1 day'),
                    3  => strtotime('-1 week'),
                    4  => strtotime('-2 weeks'),
                    5  => strtotime('-1 month'),
                    6  => strtotime('-2 months'),
                    7  => strtotime('-3 months'),
                    8  => strtotime('-4 months'),
                    9  => strtotime('-5 months'),
                    10 => strtotime('-6 months'),
                    11 => strtotime('-7 months'),
                    12 => strtotime('-8 months'),
                    13 => strtotime('-9 months'),
                    14 => strtotime('-10 months'),
                    15 => strtotime('-11 months'),
                    16 => strtotime('-1 year'),
                    17 => strtotime('-2 years'),
                    18 => strtotime('-3 years')
                ];

                $sentInvoiceEmail = [];

                $usersWithOrders = User::whereHas('orders',
                    function (Builder $q) {
                        $q->where('status', Order::SETUP);
                        $q->orWhere('status', Order::RECENT);
                        $q->whereHas('cycle', function (Builder $q) {
                            $q->where('cycle', '!=', '1');
                        });
                    })
                    ->get()
                    ->load('orders')
                    ->load('orders.cycle');

                foreach ($usersWithOrders as $user)
                {
                    $this->info('User:' . $user->email);
                    $orders = $user->orders->groupBy('cycle.cycle');

                    // This is needed to mail the invoice info.
                    $_SERVER['HTTP_HOST'] = $user->getSetting('site.url');

                    foreach ($dates as $i => $date) {
                        $date = strtotime('-' . $user->getSetting('invoice.days2send', 0) . ' days', $date);

                        if (isset($orders[$i])) {
                            $filteredOrders = $orders[$i]->filter(function ($item) use ($date) {
                                return strtotime($item->last_invoice) >= $date;
                            });

                            foreach ($filteredOrders as $order)
                            {
                                if($order->status == Order::SETUP || $order->status == Order::RECENT || $order->status == Order::SHIPPED)
                                {
                                    $cycle = $order->cycle->cycle;
                                    $orderDueDate = date_create($order->last_invoice);

                                    $addPeriod = '';
                                    if($cycle == 2) $addPeriod = '1 day';
                                    if($cycle == 3) $addPeriod = '7 days';
                                    if($cycle == 4) $addPeriod = '2 weeks';
                                    if($cycle == 5) $addPeriod = '1 month';
                                    if($cycle == 6) $addPeriod = '2 months';
                                    if($cycle == 7) $addPeriod = '3 months';
                                    if($cycle == 8) $addPeriod = '4 months';
                                    if($cycle == 9) $addPeriod = '5 months';
                                    if($cycle == 10) $addPeriod = '6 months';
                                    if($cycle == 11) $addPeriod = '7 months';
                                    if($cycle == 12) $addPeriod = '8 months';
                                    if($cycle == 13) $addPeriod = '9 months';
                                    if($cycle == 14) $addPeriod = '10 months';
                                    if($cycle == 15) $addPeriod = '11 months';
                                    if($cycle == 16) $addPeriod = '12 months';
                                    if($cycle == 17) $addPeriod = '24 months';
                                    if($cycle == 18) $addPeriod = '36 months';

                                    date_add($orderDueDate, date_interval_create_from_date_string($addPeriod));

                                    $now = date_create(date('Y-m-d h:m:s'));
                                    $diffDays = date_diff($orderDueDate, $now);

                                    if($user->getSetting('invoice.days2send') >= $diffDays->days)
                                    {
                                        if ($user->getSetting('invoice.paymentsDue')) {
                                            $dueDate = date('Y-m-d h:m:s', strtotime('+' . $user->getSetting('invoice.paymentsDue', 0) . ' days'));
                                        } else {
                                            $dueDate = date('Y-m-d h:m:s');
                                        }

                                        Invoices::create($order->user_id, $order->customer_id, $dueDate);

                                        $newInvoice = Invoices::save();
                                        $newInvoice->total = $order->price;

                                        $newInvoice->order_id = $order->id;
                                        $newInvoice->status = Invoice::UNPAID;
                                        $newInvoice->due_at = $dueDate;
                                        $newInvoice->save();

                                        if (!empty($order->customer->vat_number)) {
                                            $vatCountryIso = substr($order->customer->vat_number, 0, 2);
                                            $vatNumber = substr($order->customer->vat_number, 2, strlen($order->customer->vat_number) - 2);
                                            $oVatValidator = new VatValidator(new Europa, $vatNumber, $vatCountryIso);

                                            $otVatValidatorCheck = $oVatValidator->check();
                                        }

                                        $taxRate = 0;
                                        if($order->customer->mailingContact)
                                        {
                                          $arrRates = DB::table('taxRates')->join('taxZones','taxRates.zone_id','=','taxZones.id')
                                      			->join('taxZoneCounties','taxZoneCounties.zone_id','=','taxZones.id')
                                      			->where('taxZoneCounties.county_id','=', $order->customer->mailingContact->address->county_id)
                                      			->get();

                                      		foreach($arrRates as $key => $rate) {
                                      			$taxRate = $rate->rate;
                                      		}
                                        }

                                        $productTax = ($taxRate / 100) * $order->price;

                                        $newInvoice->tax = $productTax;
                                        $newInvoice->address_id = $newInvoice->customer->mailingContact->address->id;
                                        $newInvoice->save();

                                        $item = new InvoiceItem();
                                        $item->tax = $productTax;
                                        $item->tax_class = $order->package->tax;
                                        $item->item = $order->package->name;
                                        $item->product = $item->description = "";
                                        $item->price = $order->price;
                                        $item->quantity = 1;

                                        $newInvoice->items()->save($item);
                                        $order->last_invoice = date('Y-m-d h:m:s');
                                        $order->save();

                                        $gateway = new PaymentController();
                                        $gateway->invoice = $newInvoice;
                                        $gateway->customer = User::findOrFail($newInvoice->customer_id);
                                        $gateway->currency = Currency::findOrFail($newInvoice->currency_id);
                                        $gateway->user = $user;
                                        $paymentMethods = $gateway->getAvailablePaymentMethods();
                                        $currency = Currency::findOrFail($user->getSetting('site.defaultCurrency', 4));
                                        $subTotal = $newInvoice->total;

                                        $this->line('Send Invoice Mail : ' . (empty($paymentMethods) ? 'Manual payment' : 'AutoBill'));

                                        if (!empty($paymentMethods)) {
                                            array_push($sentInvoiceEmail, $newInvoice->id);
                                            Mail::send(
                                                'Invoices.newInvoiceAutoBillEmail',
                                                [
                                                    'user'           => $newInvoice->user,
                                                    'customer'       => $newInvoice->customer,
                                                    'invoice'        => $newInvoice,
                                                    'currency'       => $currency,
                                                    'validationHash' => Invoices::getHash($newInvoice),
                                                    'subTotal'       => $subTotal
                                                ],
                                                function ($m) use ($newInvoice, $user) {
                                                    $m->from($newInvoice->user->mailingContact->address->email, $newInvoice->user->mailingContact->address->contact_name);
                                                    $m->to($newInvoice->customer->email, $newInvoice->customer->name);
                                                    $m->subject($user->getSetting('site.name') . ' - New Invoice Due');
                                                }
                                            );
                                        } else {
                                            array_push($sentInvoiceEmail, $newInvoice->id);
                                            Mail::send(
                                                'Invoices.newInvoiceManualPaymentEmail',
                                                [
                                                    'user'           => $newInvoice->user,
                                                    'customer'       => $newInvoice->customer,
                                                    'invoice'        => $newInvoice,
                                                    'currency'       => $currency,
                                                    'validationHash' => Invoices::getHash($newInvoice),
                                                    'subTotal'       => $subTotal
                                                ],
                                                function ($m) use ($newInvoice, $user) {
                                                    $m->from($newInvoice->user->mailingContact->address->email, $newInvoice->user->mailingContact->address->contact_name);
                                                    $m->to($newInvoice->customer->email, $newInvoice->customer->name);
                                                    $m->subject($user->getSetting('site.name') . ' - New Invoice Due');
                                                }
                                            );
                                        }
                                    }
                                }
                            }

                            $this->line(count($filteredOrders) . ' FOR ' . Package_Cycle::$cycles[$i]);
                        } else {
                            $this->line('0 for ' . Package_Cycle::$cycles[$i]);
                        }
                    }

                    $this->line('');
                }

                $usersWithOptionOrders = User::whereHas('order_options', function (Builder $q) {
                    $q->where('order_options.status', Order_Options::SETUP);
                    $q->where('amount', '>', 0);
                    $q->where('cycle_type', '!=', '1');
                })->get();

                foreach ($usersWithOptionOrders as $user) {
                    $this->info('User:' . $user->email);
                    $orders = $user->orders->groupBy('cycle.cycle');

                    // This is needed to mail the invoice info.
                    $_SERVER['HTTP_HOST'] = $user->getSetting('site.url');

                    foreach ($dates as $i => $date) {
                        $date = strtotime('-' . $user->getSetting('invoice.days2send', 0) . ' days', $date);

                        if (isset($orders[$i])) {
                            $filteredOrders = $orders[$i]->filter(function ($item) use ($date) {
                                return strtotime($item->last_invoice) <= $date;
                            });

                            foreach ($filteredOrders as $order)
                            {
                                if($order->status == Order::SETUP || $order->status == Order::RECENT || $order->status == Order::SHIPPED)
                                {
                                    $orderDueDate = date_create($order->last_invoice);
                                    $now = date_create(date('Y-m-d h:m:s'));
                                    $diffDays = date_diff($orderDueDate, $now);

                                    if($user->getSetting('invoice.days2send') >= $diffDays->days)
                                    {
                                        if ($user->getSetting('invoice.paymentsDue')) {
                                            $dueDate = date('Y-m-d h:m:s', strtotime('+' . $user->getSetting('invoice.paymentsDue', 0) . ' days'));
                                        } else {
                                            $dueDate = date('Y-m-d h:m:s');
                                        }

                                        Invoices::create($order->user_id, $order->customer_id, $dueDate);

                                        Invoices::addItem($order->package->name, '', '', $order->cycle->price, 1);
                                        $newInvoice = Invoices::save();

                                        $newInvoice->order_id = $order->id;
                                        $newInvoice->due_at = $dueDate;
                                        $newInvoice->save();

                                        $order->last_invoice = date('Y-m-d h:m:s');
                                        $order->save();

                                        $gateway = new PaymentController();
                                        $gateway->invoice = $newInvoice;
                                        $gateway->customer = User::findOrFail($newInvoice->customer_id);
                                        $gateway->currency = Currency::findOrFail($newInvoice->currency_id);
                                        $gateway->user = $user;
                                        $paymentMethods = $gateway->getAvailablePaymentMethods();
                                        $currency = Currency::findOrFail($user->getSetting('site.defaultCurrency', 4));
                                        $subTotal = $newInvoice->total;

                                        if (!empty($paymentMethods)) {
                                            array_push($sentInvoiceEmail, $newInvoice->id);
                                            Mail::send(
                                                'Invoices.newInvoiceAutoBillEmail',
                                                [
                                                    'user'           => $newInvoice->user,
                                                    'customer'       => $newInvoice->customer,
                                                    'invoice'        => $newInvoice,
                                                    'currency'       => $currency,
                                                    'validationHash' => Invoices::getHash($newInvoice),
                                                    'subTotal'       => $subTotal
                                                ],
                                                function ($m) use ($newInvoice, $user) {
                                                    $m->from($newInvoice->user->mailingContact->address->email, $newInvoice->user->mailingContact->address->contact_name);
                                                    $m->to($newInvoice->customer->email, $newInvoice->customer->name);
                                                    $m->subject($user->getSetting('site.name') . ' - New Invoice Due');
                                                }
                                            );
                                        } else {
                                            array_push($sentInvoiceEmail, $newInvoice->id);
                                            Mail::send(
                                                'Invoices.newInvoiceManualPaymentEmail',
                                                [
                                                    'user'           => $newInvoice->user,
                                                    'customer'       => $newInvoice->customer,
                                                    'invoice'        => $newInvoice,
                                                    'currency'       => $currency,
                                                    'validationHash' => Invoices::getHash($newInvoice),
                                                    'subTotal'       => $subTotal
                                                ],
                                                function ($m) use ($newInvoice, $user) {
                                                    $m->from($newInvoice->user->mailingContact->address->email, $newInvoice->user->mailingContact->address->contact_name);
                                                    $m->to($newInvoice->customer->email, $newInvoice->customer->name);
                                                    $m->subject($user->getSetting('site.name') . ' - New Invoice Due');
                                                }
                                              );
                                          }
                                      }
                                  }
                              }

                            $this->line(count($filteredOrders) . ' FOR ' . Package_Cycle::$cycles[$i]);
                        } else {
                            $this->line('0 for ' . Package_Cycle::$cycles[$i]);
                        }
                    }

                    $this->line('');
                }

                //Send Reminder Invoice
                $user = User::first();
                if ($user) {
                    $daysBetweenReminders = $user->getSetting('invoice.reminders') ?: 0;
                    $remindersBeforeSuspend = $user->getSetting('invoice.reminders4suspend') ?: 0;
                    $invoices = Invoice::where('status', Invoice::UNPAID)
                        ->whereRaw("last_reminder <= CURDATE() - INTERVAL " . $daysBetweenReminders . " DAY")
                        ->whereRaw("due_at <= CURDATE()")
                        ->get();

                    foreach ($invoices as $invoice)
                    {
                        $addTimeServer = strtotime('+2 hour');

                        if(isset($invoice->order->cycle->cycle))
                          {
                            $cycle = $invoice->order->cycle->cycle;
                            $addPeriod = '';
                            if($cycle == 2) $addPeriod = '1 day';
                            if($cycle == 3) $addPeriod = '7 days';
                            if($cycle == 4) $addPeriod = '2 weeks';
                            if($cycle == 5) $addPeriod = '1 month';
                            if($cycle == 6) $addPeriod = '2 months';
                            if($cycle == 7) $addPeriod = '3 months';
                            if($cycle == 8) $addPeriod = '4 months';
                            if($cycle == 9) $addPeriod = '5 months';
                            if($cycle == 10) $addPeriod = '6 months';
                            if($cycle == 11) $addPeriod = '7 months';
                            if($cycle == 12) $addPeriod = '8 months';
                            if($cycle == 13) $addPeriod = '9 months';
                            if($cycle == 14) $addPeriod = '10 months';
                            if($cycle == 15) $addPeriod = '11 months';
                            if($cycle == 16) $addPeriod = '12 months';
                            if($cycle == 17) $addPeriod = '24 months';
                            if($cycle == 18) $addPeriod = '36 months';
                          }

                        $sendEmail = false;
                        if(isset($invoice->order->last_invoice))
                        {
                            $orderDueDate = date_create($invoice->order->last_invoice);
                            date_add($orderDueDate, date_interval_create_from_date_string($addPeriod));
                            $due = $orderDueDate;
                            $now = new \DateTime(date('Y-m-d H:m:s', $addTimeServer));
                            $daysLate = $due->diff($now)->days;
                            if($user->getSetting('invoice.days2send') <= $daysLate)
                            {
                                $sendEmail = true;
                            }
                        }

                        $reminderNumber = 0;
                        if ($daysBetweenReminders > 0) {
                            if ($daysLate % $daysBetweenReminders == 0) {
                                $reminderNumber = floor($daysLate / $daysBetweenReminders);
                            }
                        }

                        if (in_array($sentInvoiceEmail, $sentInvoiceEmail)) {
                            $sendEmail = false;
                        }

                        if($sendEmail)
                        {
                            $userFromEmail = $invoice->user->mailingContact->address->email;
                            $userFromName = $invoice->user->mailingContact->address->contact_name;
                            $subject = $user->getSetting('site.name') . ' - Payment is Due';
                            $view = 'Invoices.invoiceReminderEmail';
                            $content = [
                                'user'           => $invoice->user,
                                'customer'       => $invoice->customer,
                                'invoice'        => $invoice,
                                'currency'       => Currency::findOrFail($user->getSetting('site.defaultCurrency', 4)),
                                'validationHash' => Invoices::getHash($invoice),
                                'subTotal'       => $invoice->total,
                                'reminder'       => $reminderNumber,
                                'address'        => Address::findOrFail($invoice->address_id)
                            ];

                            Mail::to($invoice->customer)->send(new InvoiceEmail($userFromEmail, $userFromName, $subject, $content, $view));

                            $invoice->last_reminder = now();
                            $invoice->save();

                            if ($invoice->order)
                            {
                                if ($invoice->order->integration === 'directadmin')
                                {
                                    if ($invoice->suspend_count == $remindersBeforeSuspend)
                                    {
                                        $this->autoSuspend($invoice->order);
                                    }
                                    elseif ($remindersBeforeSuspend)
                                    {
                                        $invoice->increment('suspend_count');
                                    }
                                }

                                if ($invoice->order->integration === 'cpanel')
                                {
                                    if ($invoice->suspend_count == $remindersBeforeSuspend)
                                    {
                                        $this->autoSuspendCpanel($invoice->order);
                                    }
                                    elseif ($remindersBeforeSuspend)
                                    {
                                        $invoice->increment('suspend_count');
                                    }
                                }
                            }
                          }

                          $this->info("Send Reminder Email Invoice #" . $invoice->id);
                    }
                } else {
                    $this->info("User not found for the admin");
                }
            }
        }

    }

    /**
     * @return void
     */
    protected function removeTrialWhenExpired()
    {
        Order::trialExpires()
            ->get()
            ->each(function ($order) {
                $order->resetTrial();
                $this->info('Order ID : ' . $order->id . ' Trial Removed');
            });
    }

    /**
     * @param Order $order
     */
    public function autoSuspend(Order $order)
    {
        $orderSettings = $order
            ->settings
            ->pluck('setting_value', 'setting_name')
            ->all();

        $server = $orderSettings['directadmin.server'];
        $username = $orderSettings['directadmin.username'];

        // Get the DirectAdmin server settings
        $integration = Integration::find($server);
        $port = $integration->port ?? 2222;
        $host = $integration->https ? 'https://' : 'http://';
        $host .= $integration->hostname . ':' . $port;

        $directAdmin = new DirectAdmin($host, $integration->username, $integration->password);
        $success = $directAdmin->suspend($username);
        if ($success) {
            $order->status = Order::SUSPENDED;
            $order->save();
        }
    }

    public function autoSuspendCpanel(Order $order)
    {
        $orderSettings = $order
            ->settings
            ->pluck('setting_value', 'setting_name')
            ->all();

        $server = $orderSettings['cpanel.server'];
        $username = $orderSettings['cpanel.username'];
        $password = $orderSettings['cpanel.password'];

        // Get the DirectAdmin server settings
        $integrationCpanel = IntegrationCpanel::find($server);
        $cpanelHost = $integrationCpanel->https ? 'https://' : 'http://';
        $cpanelHost .= $integrationCpanel->hostname . ':' . $integrationCpanel->port;

        $cpanel = new CPanel($cpanelHost, $integrationCpanel->username, $integrationCpanel->access_key);
        $success = $cpanel->suspend($username);
        if ($success) {
            $order->status = Order::SUSPENDED;
            $order->save();
        }
    }
}
