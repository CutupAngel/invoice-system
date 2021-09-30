<?php

namespace App\Console\Commands\Crons;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Invoices;
use Settings;
use Mail;
use DateTime;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentController;
use App\Address;
use App\Currency;
use App\Site;
use App\Invoice;
use App\Integrations\DirectAdmin;
use App\Integrations\CPanel;
use App\Integration;
use App\IntegrationCpanel;
use App\Order;
use App\User;
use App\Mail\InvoiceEmail;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDOException;

class InvoiceAutoPay extends Command
{
    /**
     * @var string
     */
    protected $signature = 'BS:cron:invoiceAutoPay {--server=}';

    /**
     * @var string
     */
    protected $description = 'Bills Saved Payment Methods for Automatic Payment of Invoices and Send Emails In Relation To DueDate.';

    /**
     * InvoiceAutoPay constructor.
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
        $sites = Site::all();

        foreach($sites as $k => $site) {

          if($site->server_region_id == $this->option('server'))
          {
            $this->info("Site:" . $site->domain);

            try {
                $site->setDatabase();
                DB::reconnect('site');
                DB::setDefaultConnection('site');
            } catch (PDOException $e) {
                $this->line('Connection failed on: ' . $site->domain);
                continue;
            }

            $daysBetweenReminders = Settings::get('invoice.reminders',0);
            $lateFeePercentage = Settings::get('invoice.lateFees',0);
            $lateFeeTaxClass = Settings::get('invoice.lateFeesTax',0);

            $addTimeServer = strtotime('+2 hour');

            $userWithInvoices = User::whereExists(function ($q) use ($addTimeServer) {
                                  $q->select(DB::raw('*'))
                                    ->from('invoices')
                                    ->where('status', Invoice::UNPAID)
                                    ->where('due_at', '<=', new DateTime(date('Y-m-d H:m:s', $addTimeServer)));
                              })
                              ->get();

            $arrayList = [];
            $x = 0;
            foreach ($userWithInvoices as $user) {
                $y = 0;
                foreach ($user->invoices as $invoice) {
                    if ($invoice->status == Invoice::UNPAID) {
                        $getOrder = Order::find($invoice->order_id);

                        if ($getOrder && $getOrder->trial_order == 1) {
                            continue;
                        }

                        $due = new DateTime($invoice->due_at);
                        $now = new DateTime(date('Y-m-d H:m:s', $addTimeServer));
                        $daysLate = $due->diff($now)->days;
                        $secondsLate = $now->getTimestamp() - $due->getTimestamp();

                        $arrayList[$x][$y]['invoice_id'] = $invoice->id;
                        $arrayList[$x][$y]['late'] = $daysLate;

                        $autoChargeRetry = [0,1,2,4]; //0 days late (first charge attempt), 1 day late, 3 days late, 7 days late, for a total of 3 retries

                        $AttemptCharge = false;

                        if($secondsLate > 0) $AttemptCharge = true;

                        $sendReminderEmail = false;
                        $reminderNumber = 0;
                        if ($daysBetweenReminders > 0) {
                            if($daysLate % $daysBetweenReminders == 0) {
                                //if dayslate is a multiple of daysbetweenreminders
                                $sendReminderEmail = true;
                                $reminderNumber = floor($daysLate / $daysBetweenReminders);
                            }
                        }

                        $chargedLateFee = 0;
                        $taxTotal = 0;
                        if ($daysLate === 1 && !empty($lateFeePercentage)) {
                            foreach($invoice->totals() as $total) {
                                if($total->item === 'Tax') {
                                    $taxTotalId = $total->id;
                                    $taxTotal = $total->price;
                                    break;
                                }
                            }

                            $subTotal = $invoice->total - $taxTotal;
                            $lateFeeTotal = $lateFeePercentage / 100 * $subTotal;
                            $newSubTotal = $subTotal + $lateFeeTotal;
                            $newTaxTotal = $taxTotal;

                            if(!empty($lateFeeTaxClass)) {
                                $rate = TaxRates::join('taxZones','taxRates.zone_id','=','taxZones.id')
                                    ->join('taxZoneCounties','taxZoneCounties.zone_id','=','taxZones.id')
                                    ->where('taxZones.user_id','=',$user)
                                    ->where('taxClasses.id','=',$lateFeeTaxClass)
                                    ->where('taxZoneCounties.county_id','=',Address::where('id',$invoice->address_id)->first()->county_id);

                                if(!empty($arrRates)) {
                                    $newTaxTotal = $rate / 100 * $subTotal;
                                }

                            }
                            $newGrandTotal = $newSubTotal + $newTaxTotal;
                            $lateFee = new InvoiceTotal();
                            $lateFee->invoice_id = $invoice->id;
                            $lateFee->item = '%'.$lateFeePercentage.' Late Fee';
                            $lateFee->price = $lateFeeTotal;
                            $lateFee->save();
                            $chargedLateFee = true;
                            if(!empty($newTaxTotal)) {
                                $tax = InvoiceTotal::findOfFail($taxTotalId);
                                $tax->price = $newTaxTotal;
                                $tax->save();
                            }
                            $invoice->total = $newGrandTotal;
                            $invoice->save();
                        }

                        $sendBillingFailed = false;
                        $sendReceiptEmail = false;
                        if ($AttemptCharge) {
                            $gateway = new PaymentController();
                            $gateway->invoice = $invoice;
                            $gateway->customer = User::findOrFail($invoice->customer_id);
                            $gateway->currency = Currency::findOrFail($invoice->currency_id);
                            $gateway->user = User::findOrFail($invoice->user_id);
                            $paymentMethods = $gateway->getAvailablePaymentMethods();

                            try {
                                if(!empty($paymentMethods)) {
                                    if($paymentMethods['card']) {
                                        $savedMethod = $gateway->getUsersSavedPaymentMethodByType(0);
                                    } elseif($paymentMethods['stripe']) {
                                        $savedMethod = $gateway->getUsersSavedPaymentMethodByType(2, $invoice->customer->stripeId);
                                    } elseif($paymentMethods['bank']) {
                                        $savedMethod = $gateway->getUsersSavedPaymentMethodByType(1);
                                    }
                                    if(!empty($savedMethod)) {
                                        Controller::setInvoiceMode($invoice->id);
                                        $cart = Controller::formatCartData();
                                        $gateway->fixedDiscount = $cart['totalDiscounts'];
                                        $gateway->savedPaymentMethod = $savedMethod;

                                        if ($paymentMethods['stripe'])
                                        {
                                            $gateway->billingAddress = Address::findOrFail($user->mailingContact->address_id);
                                            $gateway->paymentMethod['type'] = '0';

                                            $paymentIntent = '';
                                            foreach($savedMethod->data as $pm_data)
                                            {
                                                try {
                                                    $paymentIntent = \Stripe\PaymentIntent::create([
                                                        'amount' => ($invoice->total + $invoice->tax) * 100, //$cart['subTotal'] * 100,
                                                        'currency' => $invoice->currency->short_name,
                                                        'customer' => $invoice->customer->stripeId,
                                                        'payment_method' => $pm_data->id,
                                                        'off_session' => true,
                                                        'confirm' => true,
                                                    ]);
                                                    break;
                                                } catch (\Stripe\Exception\CardException $e) {
                                                    // Error code will be authentication_required if authentication is needed
                                                    echo 'Error code is:' . $e->getError()->code;
                                                    $payment_intent_id = $e->getError()->payment_intent->id;
                                                    $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
                                                }
                                            }

                                            //create request
                                            if($paymentIntent != '')
                                            {
                                                $request = new Request([
                                                    'transaction_json' => json_encode($paymentIntent),
                                                    'transaction_id' => $paymentIntent->id,
                                                    'transaction_status' => $paymentIntent->status,
                                                    'paymentMethod["type"]' => '0'
                                                ]);
                                                $status = $gateway->pay($request);
                                            }
                                        }
                                        else
                                        {
                                            $gateway->billingAddress = Address::findOrFail($savedMethod->billing_address_id);
                                            $gateway->paymentMethod['type'] = '0';
                                            $status = $gateway->pay($savedMethod);
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                $this->line('Error : '. $e->getMessage());
                            }

                            $sendReceiptEmail = false;
                            $sendBillingFailed = true;
                            if(!empty($status[0])) {
                                $sendReceiptEmail = true;
                                $sendBillingFailed = false;
                                $invoice->status = Invoice::PAID;
                                $invoice->save();
                            }

                        }

                        if($sendBillingFailed) {
                            try {
                                $customerContactName = $invoice->customer->mailingContact->address->contact_name;
                                $userFromEmail = $invoice->user->mailingContact->address->email;
                                $userFromName = $invoice->user->mailingContact->address->contact_name;
                                $subject= $invoice->user->getSetting('site.name') . ' - Billing Failed';
                                $view = 'Invoices.invoicePaymentError';
                                $content = [
                                    'user' => $invoice->user,
                                    'customer' => $invoice->customer,
                                    'invoice' => $invoice,
                                    'currency' => Currency::findOrFail($user->getSetting('site.defaultCurrency', 4)),
                                    'validationHash' => Invoices::getHash($invoice),
                                    'subTotal' => $invoice->total,
                                    'reminder' => $reminderNumber,
                                    'address' => Address::findOrFail($invoice->address_id),
                                    'tax' => $taxTotal
                                ];
                                Mail::to($invoice->customer)->send(new InvoiceEmail($userFromEmail, $userFromName, $subject, $content, $view));
                            } catch (\Exception $e) {
                                $this->line('Error Send Billing failed: '. $e->getMessage());
                            }
                        }

                        if($sendReceiptEmail) {
                            $user = User::find(1);
                            $userFromEmail = $invoice->user->mailingContact->address->email;
                            $userFromName = $invoice->user->mailingContact->address->contact_name;
                            $subject= $invoice->user->getSetting('site.name') . ' - Payment Receipt';
                            $view = 'Invoices.invoiceReceiptEmail';
                            $content = [
                                'user' => $invoice->user,
                                'customer' => $invoice->customer,
                                'invoice' => $invoice,
                                'currency' => Currency::findOrFail($user->getSetting('site.defaultCurrency', 4)),
                                'validationHash' => Invoices::getHash($invoice),
                                'subTotal' => $invoice->total,
                                'reminder' => $reminderNumber,
                                'address' => Address::findOrFail($invoice->address_id),
                                'tax' => $taxTotal
                            ];
                            Mail::to($invoice->customer)->send(new InvoiceEmail($userFromEmail, $userFromName, $subject, $content, $view));

                            if (($invoice->order->integration == 'directadmin') && ($invoice->order->status == Order::SUSPENDED)) {
                                $this->autoUnSuspend($invoice->order);
                            }

                            if (($invoice->order->integration == 'cpanel') && ($invoice->order->status == Order::SUSPENDED)) {
                                $this->autoUnSuspendCpanel($invoice->order);
                            }
                        }
                        $y++;
                    }
                    $x++;
                }
            }
        }
    }
  }

    /**
     * @param Order $order
     */
    public function autoUnSuspend(Order $order)
    {
        $orderSettings = $order
            ->settings
            ->pluck('setting_value', 'setting_name')
            ->all();

        $server = $orderSettings['directadmin.server'];
        $username = $orderSettings['directadmin.username'];
        $password = $orderSettings['directadmin.password'];

        // Get the DirectAdmin server settings
        $integration = Integration::find($server);
        $port = $integration->port ?? 2222;
        $host = $integration->https ? 'https://' : 'http://';
        $host .= $integration->hostname . ':' . $port;

        $directAdmin = new DirectAdmin($host, $integration->username, $integration->password);
        $success = $directAdmin->unsuspend($username);
        if ($success) {
            $order->status = Order::SETUP;
            $order->save();
        }
    }

    public function autoUnSuspendCpanel(Order $order)
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

    		$cpanel = new CPanel(
    			$cpanelHost,
    			$integrationCpanel->username,
    			$integrationCpanel->access_key
    		);

        $success = $cpanel->unsuspend($username);
        if ($success) {
            $order->status = Order::SETUP;
            $order->save();
        }
    }
}
