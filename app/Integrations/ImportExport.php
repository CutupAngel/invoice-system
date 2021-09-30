<?php

namespace App\Integrations;

use App\Address;
use App\Counties;
use App\Countries;
use App\Invoice;
use App\Order;
use App\Order_Group;
use App\Transactions;
use App\User_Contact;
use App\User_Link;
use App\User_Setting;
use App\Package;
use App\Currency;
use App\Package_Cycle;
use App\Transaction;
use Auth;
use Settings;
use App\User;
use Laracsv\Export;
use Illuminate\Http\Request;

use App\Repositories\ActivationRepository;
use App\SupportTicket;
use App\SupportTicketMessage;
use Mail;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportExport extends DataIntegration
{
    const TITLE = 'Import/Export';
    const SHORTNAME = 'importexport';
    const DESCRIPTION = "Import/Export to and from BillingServ, backup all your data with our Import/Export Feature.";

    public $activationRepo;
    public $mailer;

    private $whmBilling = [
        'invoiceInserted' => []
    ];

    public function __construct()
    {
//        $this->activationRepo = new ActivationRepository();
        // $this->mailer = $mailer;
    }


    public static function getInfo()
    {
        return [
            'title' => self::TITLE,
            'shortname' => self::SHORTNAME,
            'description' => self::DESCRIPTION,
            'status' => self::checkEnabled()
        ];
    }

    public static function checkEnabled()
    {
        return Settings::get('integration.importexport') === '1' ? true : false;
    }

    public static function toggle()
    {
        if (self::checkEnabled()) {
            Settings::set([
                'integration.importexport' => false
            ]);
            return 0;
        }
        else
        {
            Settings::set([
                'integration.importexport' => true
            ]);
            return 1;
        }
    }

    public function getError()
    {
        // TODO: Implement getError() method.
    }

    public static function getSetupForm()
    {
        return view('Integrations.importexportSetup');
    }

    public static function setup(Request $request)
    {
        // dd(
        //      explode(',', file_get_contents($request->file('file')->getRealPath()))
        // );
        // $array = str_getcsv(
        //         file_get_contents($request->file('file')->getRealPath())
        //     );

        // foreach ($array as $item) {
        //     echo $item;
        // }

        // // dd(
        // //     str_getcsv(
        // //         file_get_contents($request->file('file')->getRealPath())
        // //     )
        // // );

        // // $csv = array_map('str_getcsv', file_get_contents($request->file('file')->getRealPath()));

        // // array_walk($csv, function(&$a) use ($csv) {
        // //     $a = array_combine($csv[0], $a);
        // // });
        // // array_shift($csv); # remove column header

        // die();

        if ($request->has('export') && $request->get('export') == 'customers') {
            return (new self)->exportCustomers($request);
        }

        if ($request->has('export') && $request->get('export') == 'transactions') {
            return (new self)->exportTransactions($request);
        }

        if ($request->has('import') && $request->get('import') == 'customers') {
            return (new self)->importCustomers($request);
        }

        if ($request->has('import') && $request->get('import') == 'transactions') {
            return (new self)->importTransactions($request);
        }

        if ($request->has('import') && $request->get('import') == 'billing') {
            return (new self)->importBilling($request);
        }
    }

    public function importBilling(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_billing' => 'required'
        ]);
		
        if ($validator->fails()) {
            return redirect()->back()->withErrors('SQL File required');
        }
		
		if (pathinfo($request->file('file_billing')->getClientOriginalName(), 4) !== 'sql') {
            return redirect()->back()->withErrors('Format is incorrect, please use correct format');
        }

        $originalFilename = $request->file('file_billing')->getClientOriginalName();
        $request->file('file_billing')->move(storage_path(), $originalFilename);
        $queries = $this->transformSQLintoArray($originalFilename);
        $queries = $this->getInsertStatement($queries);

        if ($request->get('sql_type') === "whmcs") {
            $this->processWHMSQL($queries);
        }

        if ($request->get('sql_type') === "blesta") {
            $this->processBlestaSql($queries);
        }

        return redirect()->back()->with('message', 'Import data done!');
    }

    private function processWHMSQL(array $data)
    {
        session()->put('_status_log', 'Import data initiate');
        DB::beginTransaction();

        if (isset($data['tblclients'])) {
            foreach ($data['tblclients'] as $client) {
                $user = new User();

                $user->name = $client['firstname'] . " " . $client['lastname'];
                $user->email = $client['email'];
                $user->account_type = User::CUSTOMER;
                $user->password = $client['password'];
                $user->created_at = Carbon::parse(trim($client['datecreated']));
                $user->username = $client['email'];

                try {
                    $user->save();
                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    return redirect()->back()->withErrors($exception->getMessage());
                }

                $address = new Address();

                $address->contact_name = $client['firstname'] . " " . $client['lastname'];
                $address->business_name = $client['companyname'] ? $client['companyname'] : "";
                $address->phone = $client['phonenumber'];
                $address->fax = $address->address_3 = $address->address_4 = $address->website = "";
                $address->email = $client['email'];
                $address->address_1 = $client['address1'];
                $address->address_2 = $client['address2'];
                $address->city = $client['city'];
                $address->postal_code = $client['postcode'];
                $country = Countries::select('id')->where('iso2', 'like', "%" . strtoupper($client['country']) . "%")->first();

                $address->country_id = $country->id;
                $address->created_at = Carbon::parse($client['created_at']);
                $address->updated_at = Carbon::parse($client['updated_at']);

                try {
                    $address->save();
                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    return redirect()->back()->withErrors($exception->getMessage());
                }

                $userContact = new User_Contact();
                $userContact->user_id = $user->id;
                $userContact->address_id = $address->id;
                $userContact->type = '1';

                try {
                    $userContact->save();
                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    return redirect()->back()->withErrors($exception->getMessage());
                }

                if (isset($data['tblproductgroups'])) {
                    foreach ($data['tblproductgroups'] as $productGroup) {
                        $orderGroup = new Order_Group();

                        $orderGroup->user_id = Auth::user()->id;
                        $orderGroup->name = $productGroup['name'];
                        $orderGroup->description = $productGroup['headline'] . " " . $productGroup['tagline'];
                        $orderGroup->url = Str::slug($productGroup['name']);
                        $orderGroup->visible = (int)$productGroup['hidden'] === 0 ? 1 : 0;
                        $orderGroup->created_at = $productGroup['created_at'];
                        $orderGroup->updated_at = $productGroup['updated_at'];
                        $orderGroup->type = 2;

                        try {
                            $orderGroup->save();
                        } catch (\Exception $exception) {
                            DB::rollBack();
                            Log::warning($exception->getMessage());
                            return redirect()->back()->withErrors($exception->getMessage());
                        }

                        if (isset($data['tblproducts']) && isset($data['tblpricing'])) {
                            $products = collect($data['tblproducts'])->where('gid', $orderGroup->id);

                            $products->each(function ($product) use ($data, $orderGroup, $address, $user, $client) {
                                $package = new Package();

                                $package->group_id = $orderGroup->id;
                                $package->name = $product['name'];
                                $package->description = $product['description'];
                                $package->tax = $product['tax'];
                                $package->prorate = $product['proratabilling'];
                                $package->trial = $package->theme = $package->type = 0;
                                $package->url = $orderGroup->id . "/";
                                $package->integration = "";
                                $package->created_at = $package['created_at'];
                                $package->updated_at = $package['updated_at'];

                                try {
                                    $package->save();

                                    DB::connection('site')->table('order_group_packages')->where('id', $package->id)
                                        ->update([
                                            'url' => $orderGroup->id . "/" . $package->id
                                        ]);
                                    DB::commit();
                                } catch (\Exception $exception) {
                                    DB::rollBack();
                                    Log::warning($exception->getMessage());
                                    return redirect()->back()->withErrors($exception->getMessage());
                                }

                                try {
                                    $package->save();
                                } catch (\Exception $exception) {
                                    DB::rollBack();
                                    Log::warning($exception->getMessage());
                                    return redirect()->back()->withErrors($exception->getMessage());
                                }

                                $pricings = collect($data['tblpricing'])
                                    ->where('relid', (int)$product['id'])
                                    ->where('type', 'product');

                                $pricings->each(function ($pricing) use ($package, $data, $address, $product, $user, $client) {

                                    # DEVs note
                                    $billPeriod = [
                                        'setupfee' => [
                                            1  => 'msetupfee',
                                            7  => 'qsetupfee',
                                            10 => 'ssetupfee',
                                            16 => 'asetupfee',
                                            17 => 'bsetupfee',
                                            18 => 'tsetupfee'
                                        ],
                                        'price'    => [
                                            1  => "monthly",
                                            7  => "quarterly",
                                            10 => "semiannually",
                                            16 => "annually",
                                            17 => "biennially",
                                            18 => "triennially",
                                        ]
                                    ];

                                    foreach ($billPeriod['price'] as $key => $period) {
                                        $cycle = new Package_Cycle();

                                        // package_id, price, fee, cycle type, created_at, updated_at
                                        $cycle->package_id = $package->id;
                                        $cycle->price = $pricing[$period];
                                        $cycle->fee = $billPeriod['setupfee'][$key];
                                        $cycle->cycle = $key;

                                        try {
                                            $cycle->save();
                                            DB::commit();
                                        } catch (\Exception $exception) {
                                            DB::rollBack();
                                            return redirect()->back()->withErrors($exception->getMessage());
                                        }
                                    }

                                    # Work with recurring
                                    if (isset($data['tblhosting']) && isset($data['tblorders'])) {
                                        foreach ($data['tblhosting'] as $tblHosting) {
                                            $order = collect($data['tblorders'])->where('id', $tblHosting['orderid'])->first();
                                            $packageId = collect($data['tblproducts'])->where('id', $tblHosting['packageid'])->first();

                                            if ($product['id'] !== $tblHosting['packageid']) continue;

                                            if (!isset($data['tblinvoices']) || !isset($data['tblinvoiceitems'])) continue;
                                            if (!$order && !$packageId) continue;

                                            $ord = new Order();
                                            $currency = Currency::where('short_name', 'like', "%" . strtoupper($data['tblcurrencies'][0]['code']) . "%")->first();
                                            $customer = isset($data['tblclients']) ?
                                                collect($data['tblclients'])->where('id', $tblHosting['userid'])->first()
                                                : null;

                                            if (!$customer) continue;

                                            $invoice = collect($data['tblinvoices'])->where('id', $order['invoiceid'])->first();
                                            $this->whmBilling['invoiceInserted'][] = $invoice['id'];

                                            $ord->user_id = Auth::user()->id;
                                            $ord->customer_id = $user->id;
                                            $ord->package_id = $package->id;
                                            $ord->status = Invoice::getStatusConst($invoice['status']);
                                            $ord->price = $order['amount'];
                                            $ord->currency_id = $currency->id;

                                            $billPeriodPrice = array_flip($billPeriod['price']);

                                            $cc = $billPeriodPrice[strtolower($tblHosting['billingcycle'])];

                                            $orderCycle = Package_Cycle::select('id')
                                                ->where([
                                                    'package_id' => $package->id,
                                                    'cycle'      => $cc
                                                ])->first();

                                            $ord->cycle_id = $orderCycle->id;

                                            try {
                                                $ord->save();
                                            } catch (\Exception $exception) {
                                                DB::rollBack();
                                                Log::warning($exception->getMessage());
                                                return redirect()->back()->withErrors($exception->getMessage());
                                            }

                                            if ($client['id'] !== $invoice['userid']) continue;

                                            $inv = new Invoice();

                                            $inv->order_id = $ord->id;
                                            $inv->due_at = $invoice['duedate'];
                                            $inv->user_id = Auth::user()->id;
                                            $inv->customer_id = $user->id;
                                            $inv->currency_id = $currency->id;
                                            $inv->invoice_number = $invoice['invoicenum'];
                                            $inv->total = $invoice['total'];
                                            $inv->credit = $invoice['credit'];
                                            $inv->status = Invoice::getStatusConst($invoice['status']);
                                            $inv->last_reminder = $invoice['last_capture_attempt'];
                                            $inv->created_at = $invoice['created_at'];
                                            $inv->updated_at = $invoice['updated_at'];
                                            $inv->deleted_at = null;
                                            $inv->estimate = 0;
                                            $inv->tax_exempt = $invoice['tax2'];
                                            $inv->tax = $invoice['tax'];
                                            $inv->comments = $invoice['notes'];
                                            $inv->address_id = $address->id;

                                            try {
                                                $inv->save();
                                            } catch (\Exception $exception) {
                                                DB::rollBack();
                                                Log::warning($exception->getMessage());
                                                return redirect()->back()->withErrors($exception->getMessage());
                                            }

                                            if (isset($data['tblinvoiceitems'])) {
                                                $items = [];

                                                foreach ($data['tblinvoiceitems'] as $it) {
                                                    if ((int)$invoice['id'] === (int) $it['invoiceid']) {
                                                        $items[] = [
                                                            'invoice_id'  => $inv->id,
                                                            'description' => $it['description'],
                                                            'price'       => $it['amount'],
                                                            'quantity'    => 1,
                                                            'created_at'  => $inv->created_at,
                                                            'updated_at'  => $inv->updated_at
                                                        ];
                                                    }
                                                }

                                                try {
                                                    DB::connection('site')->table('invoice_items')->insert($items);
                                                    DB::commit();
                                                } catch (\Exception $exception) {
                                                    DB::rollBack();
                                                    Log::warning($exception->getMessage());
                                                    return redirect()->back()->withErrors($exception->getMessage());
                                                }
                                            }
                                        }
                                    }
                                });
                            });
                        }
                    }
                }

                $invoices = collect($data['tblinvoices'])->whereNotIn('id', $this->whmBilling['invoiceInserted']);
                $currency = Currency::where('short_name', 'like', "%" . strtoupper($data['tblcurrencies'][0]['code']) . "%")->first();

                $invoices->each(function ($invoice) use ($currency, $user, $address, $client) {
                    if ($invoice['userid'] !== $client['id']) return true;

                    $inv = new Invoice();

                    $inv->order_id = null;
                    $inv->due_at = $invoice['duedate'];
                    $inv->user_id = Auth::user()->id;
                    $inv->customer_id = $user->id;
                    $inv->currency_id = $currency->id;
                    $inv->invoice_number = $invoice['invoicenum'];
                    $inv->total = $invoice['total'];
                    $inv->credit = $invoice['credit'];
                    $inv->status = Invoice::getStatusConst($invoice['status']);
                    $inv->last_reminder = $invoice['last_capture_attempt'];
                    $inv->created_at = $invoice['created_at'];
                    $inv->updated_at = $invoice['updated_at'];
                    $inv->deleted_at = null;
                    $inv->estimate = 0;
                    $inv->tax_exempt = $invoice['tax2'];
                    $inv->tax = $invoice['tax'];
                    $inv->comments = $invoice['notes'];
                    $inv->address_id = $address->id;

                    try {
                        $inv->save();
                    } catch (\Exception $exception) {
                        DB::rollBack();
                        Log::warning($exception->getMessage());
                        return redirect()->back()->withErrors($exception->getMessage());
                    }

                    if (isset($data['tblinvoiceitems'])) {
                        $items = [];

                        foreach ($data['tblinvoiceitems'] as $it) {
                            if ((int)$invoice['id'] === (int) $it['invoiceid']) {
                                $items[] = [
                                    'invoice_id'  => $inv->id,
                                    'description' => $it['description'],
                                    'price'       => $it['amount'],
                                    'quantity'    => 1,
                                    'created_at'  => $inv->created_at,
                                    'updated_at'  => $inv->updated_at
                                ];
                            }
                        }

                        try {
                            DB::connection('site')->table('invoice_items')->insert($items);
                            DB::commit();
                        } catch (\Exception $exception) {
                            DB::rollBack();
                            Log::warning($exception->getMessage());
                            return redirect()->back()->withErrors($exception->getMessage());
                        }
                    }
                });
            }

        }

        if (isset($data['tbltickets'])) {
            foreach ($data['tbltickets'] as $ticket) {
                $tcket = new SupportTicket();

                $tcket->user_id = Auth::user()->id;
                $tcket->subject = $ticket['title'];
                $tcket->status = strtolower($ticket['status']);
                $tcket->priority = strtolower($ticket['urgency']);
                $tcket->last_action = Carbon::parse($ticket['lastreply']);
                $tcket->created_at = $ticket['date'];

                try {
                    $tcket->save();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    Log::warning($exception->getMessage());
                    return redirect()->back()->withErrors($exception->getMessage());
                }

                if (isset($data['tblticketreplies'])) {
                    $replies = collect($data['tblticketreplies'])->where('tid', $ticket['tid']);

                    $replies->each(function ($reply) use ($tcket) {
                        $repl = new SupportTicketMessage();

                        $repl->support_ticket_id = $tcket->id;
                        $repl->replay_by = $reply['userid'];
                        $repl->message = $reply['message'];
                        $repl->created_at = $reply['date'];

                        try {
                            $repl->save();
                        } catch (\Exception $exception) {
                            DB::rollBack();
                            Log::warning($exception->getmessage());
                            return redirect()->back()->withErrors($exception->getMessage());
                        }
                    });

                }
            }
        }

        $currency = 0;

        if (isset($data['tblcurrencies'])) {
            $lastCurrency = User_Setting::select('name')
                ->where('name', 'like', '%invoices.currency%')
                ->latest('name')
                ->first();

            if ($lastCurrency) {
                $lastName = (substr($lastCurrency->name, -1) + 1);
            } else {
                $lastName = 1;
            }

            foreach ($data['tblcurrencies'] as $currency) {
                $currency = Currency::where('short_name', 'like', "%" . strtoupper($currency['code']) . "%")->first();

                $userSetting = new User_Setting();
                $userSetting->user_id = Auth::user()->id;
                $userSetting->name = "invoice.currency." . $lastName;
                $userSetting->value = $currency->id;

                try {
                    $userSetting->save();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    Log::warning($exception->getMessage());
                    return redirect()->back()->withErrors($exception->getMessage());
                }
            }
        }

        return redirect()->back()->with('message', 'Import completed successfully!');
    }

    private function processBlestaSql(array $data)
    {
        session()->put('_status_log', 'Import data initiate');
        DB::beginTransaction();

        if (isset($data['users']))
        {
            foreach ($data['users'] as $client)
            {
                $email = '';
                $first_name = '';
                $last_name = '';
                foreach ($data['contacts'] as $contact)
                {
                    if($client['id'] == $contact['client_id'])
                    {
                        $first_name = $contact['first_name'];
                        $last_name = $contact['last_name'];
                        $email = $contact['email'];
                    }
                }

                $user = User::where('username', $client['username'])
                              ->orWhere('email', $email)
                              ->first();

                if(!$user)
                {
                    $user = new User();
                }

                $user->name = $first_name . " " . $last_name;
                $user->email = $email;
                $user->account_type = User::CUSTOMER;
                $user->password = $client['password'];
                $user->created_at = Carbon::parse(trim($client['date_added']));
                $user->username = $client['username'];

                try
                {
                    $user->save();
                    DB::commit();
                }
                catch (\Exception $exception)
                {
                    DB::rollBack();
                    return redirect()->back()->withErrors($exception->getMessage());
                }

                $address = new Address();
                foreach ($data['contacts'] as $contact)
                {
                    if($client['id'] == $contact['client_id'])
                    {
                        $address->contact_name = $contact['first_name'] . " " . $contact['last_name'];
                        $address->business_name = $contact['company'] ? $contact['company'] : "";
                        $address->email = $contact['email'];
                        $address->address_1 = $contact['address1'];
                        $address->address_2 = $contact['address2'];
                        $address->city = $contact['city'];
                        $address->postal_code = $contact['zip'];

                        $country = Countries::select('id')->where('iso2', 'like', "%" . strtoupper($contact['country']) . "%")->first();

                        $address->country_id = $country->id;
                        $address->created_at = Carbon::parse($client['date_added']);
                        $address->updated_at = Carbon::parse($client['date_added']);
                    }

                    foreach ($data['contact_numbers'] as $contact_number)
                    {
                        if($contact['id'] == $contact_number['contact_id'])
                        {
                            if($contact_number['type'] == 'phone')
                            {
                                $address->phone = $contact_number['number'];
                            }
                            if($contact_number['type'] == 'fax')
                            {
                                $address->fax = $contact_number['fax'];
                            }
                        }
                    }
                }

                try
                {
                    $address->save();
                    DB::commit();
                }
                catch (\Exception $exception)
                {
                    DB::rollBack();
                    return redirect()->back()->withErrors($exception->getMessage());
                }

                $userContact = new User_Contact();
                $userContact->user_id = $user->id;
                $userContact->address_id = $address->id;
                $userContact->type = '1';

                try
                {
                    $userContact->save();
                    DB::commit();
                }
                catch (\Exception $exception)
                {
                    DB::rollBack();
                    return redirect()->back()->withErrors($exception->getMessage());
                }

                if (isset($data['package_groups']))
                {
                    foreach ($data['package_groups'] as $packageGroup)
                    {
                        $orderGroup = new Order_Group();

                        $orderGroup->user_id = Auth::user()->id;
                        $orderGroup->name = $packageGroup['name'];
                        $orderGroup->description = $packageGroup['description'];
                        $orderGroup->url = Str::slug($packageGroup['name']);
                        $orderGroup->visible = (int)$packageGroup['hidden'] === 0 ? 1 : 0;
                        $orderGroup->type = 2;

                        try
                        {
                            $orderGroup->save();
                        }
                        catch (\Exception $exception)
                        {
                            DB::rollBack();
                            Log::warning($exception->getMessage());
                            return redirect()->back()->withErrors($exception->getMessage());
                        }

                        if (isset($data['packages']) && isset($data['package_pricing']))
                        {
                            $package = collect($data['package_group'])->where('package_group_id', $packageGroup['id'])->first();

                            $packages = collect($data['packages'])->where('id', $package['package_id']);

                            $packages->each(function ($package) use ($data, $orderGroup, $address, $user, $client)
                            {
                                $orderPackage = new Package();

                                $orderPackage->group_id = $orderGroup->id;
                                $orderPackage->name = $package['name'];
                                $orderPackage->description = $package['description'];
                                $orderPackage->tax = $package['taxable'];
                                $orderPackage->prorate = $package['prorata_day'];
                                $orderPackage->trial = $orderPackage->theme = $orderPackage->type = 0;
                                $orderPackage->url = $orderGroup->id . "/";
                                $orderPackage->integration = "";

                                try
                                {
                                    $orderPackage->save();

                                    DB::connection('site')->table('order_group_packages')->where('id', $orderPackage->id)
                                        ->update([
                                            'url' => $orderGroup->id . "/" . $orderPackage->id
                                        ]);
                                    DB::commit();
                                }
                                catch (\Exception $exception)
                                {
                                    DB::rollBack();
                                    Log::warning($exception->getMessage());
                                    return redirect()->back()->withErrors($exception->getMessage());
                                }

                                try
                                {
                                    $orderPackage->save();
                                }
                                catch (\Exception $exception)
                                {
                                    DB::rollBack();
                                    Log::warning($exception->getMessage());
                                    return redirect()->back()->withErrors($exception->getMessage());
                                }

                                $pricings = collect($data['package_pricing'])
                                    ->where('package_id', (int)$package['id']);

                                $pricings->each(function ($pricing) use ($orderPackage, $data, $address, $package, $user, $client)
                                {
                                    # DEVs note
                                    $billPeriod = [
                                        'setupfee' => [
                                            1  => 'msetupfee',
                                            7  => 'qsetupfee',
                                            10 => 'ssetupfee',
                                            16 => 'asetupfee',
                                            17 => 'bsetupfee',
                                            18 => 'tsetupfee'
                                        ],
                                        'price'    => [
                                            1  => "monthly",
                                            7  => "quarterly",
                                            10 => "semiannually",
                                            16 => "annually",
                                            17 => "biennially",
                                            18 => "triennially",
                                        ]
                                    ];

                                    $pricing_data = collect($data['pricings'])
                                        ->where('id', (int)$pricing['pricing_id'])->first();

                                    foreach ($billPeriod['price'] as $key => $period)
                                    {
                                        $cycle = new Package_Cycle();
                                        // package_id, price, fee, cycle type, created_at, updated_at
                                        $cycle->package_id = $orderPackage->id;
                                        $cycle->price = $pricing_data['price'];
                                        $cycle->fee = $billPeriod['setupfee'][$key];
                                        $cycle->cycle = $key;

                                        try
                                        {
                                            $cycle->save();
                                            DB::commit();
                                        }
                                        catch (\Exception $exception)
                                        {
                                            DB::rollBack();
                                            return redirect()->back()->withErrors($exception->getMessage());
                                        }
                                    }

                                    # Work with recurring
                                    if (isset($data['orders']))
                                    {
                                        foreach ($data['orders'] as $order)
                                        {
                                            $ord = new Order();
                                            $ord->user_id = Auth::user()->id;
                                            $ord->customer_id = $user->id;
                                            $ord->package_id = $package->id;
                                            $ord->cycle_id = $cycle->id;
                                            $ord->status = $order['status'];

                                            $price = 0;
                                            $currency = '';
                                            $period = '';

                                            $pricing = collect($data['pricings'])->where('id', $service->pricing_id)->first();

                                            $price = $pricing['price'];
                                            if($pricing['period'] == 'year')
                                            {
                                                $period = 'annually';
                                            }

                                            $currency = Currency::where('short_name', 'like', "%" . strtoupper($pricing['currency']) . "%")->first();

                                            $ord->price = $price;
                                            $ord->currency_id = $currency->id;

                                            $billPeriodPrice = array_flip($billPeriod['price']);

                                            $cc = $billPeriodPrice[strtolower($period)];

                                            $orderCycle = Package_Cycle::select('id')
                                                ->where([
                                                    'package_id' => $orderPackage->id,
                                                    'cycle'      => $cc
                                                ])->first();

                                            $ord->cycle_id = $orderCycle->id;
                                            $ord->created_at = $order['date_added'];

                                            try
                                            {
                                                $ord->save();
                                            }
                                            catch (\Exception $exception)
                                            {
                                                DB::rollBack();
                                                Log::warning($exception->getMessage());
                                                return redirect()->back()->withErrors($exception->getMessage());
                                            }

                                          }
                                    }

                                    if (isset($data['services']))
                                    {
                                        foreach ($data['services'] as $service)
                                        {
                                            $ord = new Order();
                                            $ord->user_id = Auth::user()->id;
                                            $ord->customer_id = $service['client_id'];
                                            $ord->package_id = $service['package_group_id'];
                                            $ord->cycle_id = $cycle->id;
                                            $ord->status = $service['status'];

                                            $price = 0;
                                            $currency = '';
                                            $period = '';

                                            $pricing = collect($data['pricings'])->where('id', $service['pricing_id'])->first();

                                            $price = $pricing['price'];
                                            if($pricing['period'] == 'year')
                                            {
                                                $period = 'annually';
                                            }
                                            $currency = Currency::where('short_name', 'like', "%" . strtoupper($pricing['currency']) . "%")->first();

                                            $ord->price = $price;
                                            $ord->currency_id = $currency->id;

                                            $billPeriodPrice = array_flip($billPeriod['price']);

                                            $cc = $billPeriodPrice[strtolower($period)];

                                            $orderCycle = Package_Cycle::select('id')
                                                ->where([
                                                    'package_id' => $orderPackage->id,
                                                    'cycle'      => $cc
                                                ])->first();
                                            $ord->cycle_id = $orderCycle->id;
                                            $ord->created_at = $service['date_added'];

                                            try
                                            {
                                                $ord->save();
                                            }
                                            catch (\Exception $exception)
                                            {
                                                DB::rollBack();
                                                Log::warning($exception->getMessage());
                                                return redirect()->back()->withErrors($exception->getMessage());
                                            }

                                          }
                                      }

                                  });
                            });
                        }
                    }
                }
            }
        }

        foreach($data['invoices'] as $invoice)
        {
            $inv = new Invoice();

            $currency = Currency::where('short_name', 'like', "%" . strtoupper($invoice['currency']) . "%")->first();

            $inv->due_at = $invoice['date_due'];
            $inv->user_id = Auth::user()->id;
            $inv->customer_id = $user->id;
            $inv->address_id = $address->id;
            $inv->currency_id = $currency->id;
            $inv->invoice_number = $invoice['id'];
            $inv->total = $invoice['total'];

            $invoiceStatus = '';
            if($invoice['status'] == 'active')
                $invoiceStatus = Invoice::UNPAID;
            $inv->status = $invoiceStatus;
            $inv->created_at = $invoice['date_billed'];

            try
            {
                $inv->save();
            }
            catch (\Exception $exception)
            {
                DB::rollBack();
                Log::warning($exception->getMessage());
                return redirect()->back()->withErrors($exception->getMessage());
            }

            if (isset($data['invoice_lines']))
            {
                $items = [];

                foreach ($data['invoice_lines'] as $it)
                {
                    if ((int)$invoice['id'] === (int) $it['invoice_id'])
                    {
                        $items[] = [
                            'invoice_id'  => $inv->id,
                            'description' => $it['description'],
                            'price'       => $it['amount'],
                            'quantity'    => 1,
                            'created_at'  => $inv->created_at,
                            'updated_at'  => $inv->updated_at
                        ];
                    }
                }

                try
                {
                    DB::connection('site')->table('invoice_items')->insert($items);
                    DB::commit();
                }
                catch (\Exception $exception)
                {
                    DB::rollBack();
                    Log::warning($exception->getMessage());
                    return redirect()->back()->withErrors($exception->getMessage());
                }
            }
            if($inv->status == Invoice::PAID)
            {
                if (isset($data['transactions']))
                {
                    foreach ($data['transactions'] as $trans)
                    {
                        $transaction = new Transaction();
                        $transaction->id = $trans['id'];
                        $transaction->invoice_id = $inv->id;
                        $transaction->user_id = Auth::user()->id;
                        $transaction->customer_id = $user->id;

                        $currency = Currency::where('short_name', 'like', "%" . strtoupper($trans['currency']) . "%")->first();

                        $transaction->currency_id = $currency->id;
                        $transaction->amount = $trans['amount'];
                        $transaction->payment_method = 1;
                        $transaction->status = $trans['status'];
                        $transaction->created_at = $trans['date_added'];
                        $transaction->transaction_key = $trans['reference_id'];

                        try
                        {
                            $transaction->save();
                        }
                        catch (\Exception $exception)
                        {
                            DB::rollBack();
                            Log::warning($exception->getMessage());
                            return redirect()->back()->withErrors($exception->getMessage());
                        }
                    }
                }
            }
        }

        if (isset($data['support_tickets']))
        {
            foreach ($data['support_tickets'] as $ticket)
            {
                $tcket = new SupportTicket();

                $tcket->user_id = Auth::user()->id;
                $tcket->assignee_by = $user->id;
                $tcket->subject = $ticket['summary'];
                $tcket->status = strtolower($ticket['status']);
                $tcket->priority = strtolower($ticket['priority']);
                $tcket->created_at = $ticket['date_added'];
                $tcket->updated_at = $ticket['date_updated'];

                try
                {
                    $tcket->save();
                }
                catch (\Exception $exception)
                {
                    DB::rollBack();
                    Log::warning($exception->getMessage());
                    return redirect()->back()->withErrors($exception->getMessage());
                }

                if (isset($data['support_replies']))
                {
                    $replies = collect($data['support_replies'])->where('ticket_id', $ticket['id']);

                    $replies->each(function ($reply) use ($tcket) {
                        $repl = new SupportTicketMessage();

                        $repl->support_ticket_id = $tcket->id;
                        $repl->replay_by = $reply['staff_id'];
                        $repl->message = $reply['details'];
                        $repl->created_at = $reply['date_added'];

                        try {
                            $repl->save();
                        } catch (\Exception $exception) {
                            DB::rollBack();
                            Log::warning($exception->getmessage());
                            return redirect()->back()->withErrors($exception->getMessage());
                        }
                    });

                  }
              }
          }

        return redirect()->back()->with('message', 'Import completed successfully!');
    }
    private function transformSQLintoArray($file)
    {
        $opData = '';
        $queries = [];
        $result = file(storage_path($file));

        if (!$result) {
            return redirect()->back()->withErrors('File not found.');
        }

        foreach($result as $line) {
            if (substr($line, 0, 2) == '--' || $line == '') continue;

            $opData .= $line;

            if (substr(trim($line), -1, 1)) {
                $queries[] = $opData;
                $opData = '';
            }
        }

        foreach ($queries as $key => $query) {
            $queries[$key] = str_replace(PHP_EOL, "", $query);
        }

        return $queries;
    }

    private function getInsertStatement($queries)
    {
        $insertValueQueries = [];

        # Get only insert into values
        foreach ($queries as $key => $query) {
            if (strpos($query, "INSERT INTO") !== false) {
                $tmpText = substr($query, strpos($query, '`') + 1, strlen($query));
                $insertValueQueries[] = substr($tmpText, 0, strpos($tmpText, '`'));
            } else {
                continue;
            }
        }

        foreach ($insertValueQueries as $key => $table) {
            $tmpQueries = $queries;

            foreach ($tmpQueries as $pointer => $query) {
                if (strpos($query, "INSERT INTO `$table`") === false) {
                    unset($tmpQueries[$pointer]);
                } else {
                    break;
                }
            }

            $values = "";

            $tmpQueries = array_values($tmpQueries);

            $header = "";

            foreach ($tmpQueries as $pointer => $query) {
                if ($pointer === 0) {
                    $header = substr($query, strpos($query, "(") + 1);
                    $header = substr($header, 0, strpos($header, ")"));
                    continue;
                }

                if (strpos(trim($query), ";") !== false) {
                    $values .= $query;
                    break;
                } else {
                    $values .= $query;
                }
            }

            $values = explode("),",
                str_replace("(", "",
                    substr(
                        $values,
                        strpos($values, "("),
                        strlen($values) - strpos($values, ";") - 3
                    )
                )
            );

            $header = explode(",", str_replace(" ", "", str_replace("`", "", $header)));

            foreach ($values as $pointer => $value) {
                $explode = preg_split("/,(?=(?:[^']*'[^']*')*[^']*$)/ ", $value);

                foreach ($explode as $kk => $x) {
                    $x = $x === "''" ? null : (is_numeric($x) ? intval($x) : str_replace("'", "", $x));
                    $x = trim($x);
                    try {
                        $insertValueQueries[$table][$pointer][$header[$kk]] = $x;
                    } catch (\Exception $exception) {
                        continue;
                    }
                }
            }

            unset($insertValueQueries[$key]);
        }

        return $insertValueQueries;
    }

    public function exportCustomers(Request $request)
    {
        \Debugbar::disable();
        $customers = Auth::User()->load('parent.customers')
            ->parent()->first()
            ->customers()
            ->with('mailingContact.address.county')
            // ->where('created_at', '>=', $request->get('from'))
            // ->where('created_at', '<=', $request->get('to'))
            ->get();
        if ($customers->count() == 0) {
            return redirect()->back()->with([
                'customers-massage' => 'No user during this period!'
            ])->withInput($request->all());
        }

        $csvExporter = new Export();

        $csvExporter->beforeEach(function ($customer) {
            $addressModel = $customer->mailingContact->address;

            $arrayName = explode(' ', $addressModel->contact_name);

            if (!empty($arrayName[0])) {
                $customer->first_name = $arrayName[0];
            }
            if (!empty($arrayName[1])) {
                $customer->last_name = $arrayName[1];
            }
            $customer->business_name = $addressModel->business_name;
            $customer->email = $addressModel->email;
            $customer->phone = $addressModel->phone;
            $customer->address_1 = $addressModel->address_1;
            $customer->address_2 = $addressModel->address_2;
            $customer->address_3 = $addressModel->address_3;
            $customer->address_4 = $addressModel->address_4;
            $customer->county = $addressModel->county->name;
            $customer->city = $addressModel->city;
            $customer->postal_code = $addressModel->postal_code;
            $customer->country = @$addressModel->country->name;
        });

        //header('Content-Type: text/csv');
        //header('Content-Disposition: attachment; filename="customers.csv"');

        $data = [];

        $columns = [
            'First Name',
            'Last Name',
            'Business Name',
            'Email',
            'Phone',
            'Address 1',
            'Address 2',
            'Address 3',
            'Address 4',
            'County',
            'City',
            'Postcode',
            'Country',
        ];

        $data = array_merge($data, [$columns]);

        foreach ($customers as $customer) {
            $data[] = $this->generateForCustomer($customer);
        }

        /* $fp = fopen('php://output', 'wb');
        foreach ($data as $line) {
            fputcsv($fp, $line, ',');
        }
        fclose($fp); */

        //die();

        // // Redirect output to a client’s web browser (Excel5)
        // header('Content-type: text/csv');
        // header('Content-Disposition: attachment;filename="export.csv"');
        // header('Cache-Control: max-age=0');

        // dd(
        //     (string) $csvExporter->build($customers, [
        //         'first_name' => 'First Name',
        //         'last_name' => 'Last Name',
        //         'business_name' => 'Business Name',
        //         'email' => 'Email',
        //         'phone' => 'Phone',
        //         'address_1' => 'Address 1',
        //         'address_2' => 'Address 2',
        //         'address_3' => 'Address 3',
        //         'address_4' => 'Address 4',
        //         'county' => 'County',
        //         'city' => 'City',
        //         'postal_code' => 'Postcode',
        //         'country' => 'Country',
        //     ], false)->getCsv()
        // );

        header('Content-type: application/csv');
        header('Content-Disposition: attachment;filename="export.csv"');
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Content-Transfer-Encoding: UTF-8");

        $text = $csvExporter->build($customers, [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'business_name' => 'Business Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'address_1' => 'Address 1',
            'address_2' => 'Address 2',
            'address_3' => 'Address 3',
            'address_4' => 'Address 4',
            'county' => 'County',
            'city' => 'City',
            'postal_code' => 'Postcode',
            'country' => 'Country',
        ])->getCsv();

        return strip_tags(html_entity_decode($text));
    }

    public function generateForCustomer($customer, $data = [])
    {
        $addressModel = $customer->mailingContact->address;
        $arrayName = explode(' ', $addressModel->contact_name);

        $data['first_name'] = '';
        if (!empty($arrayName[0])) {
            $data['first_name'] = $arrayName[0];
        }
        $data['last_name'] = '';
        if (!empty($arrayName[1])) {
            $data['last_name'] = $arrayName[1];
        }
        $data['business_name'] = $addressModel->business_name;
        $data['email'] = $addressModel->email;
        $data['phone'] = $addressModel->phone;
        $data['address_1'] = $addressModel->address_1;
        $data['address_2'] = $addressModel->address_2;
        $data['address_3'] = $addressModel->address_3;
        $data['address_4'] = $addressModel->address_4;
        $data['county'] = $addressModel->county->name;
        $data['city'] = $addressModel->city;
        $data['postal_code'] = $addressModel->postal_code;
        $data['country'] = @$addressModel->country->name;

        return $data;
    }

    public function download_csv_results($results, $name = NULL)
    {
        if (!$name) {
            $name = md5(uniqid() . microtime(TRUE) . mt_rand()) . '.csv';
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Pragma: no-cache');
        header("Expires: 0");

        $outstream = fopen("php://output", "wb");

        foreach ($results as $result) {
            fputcsv($outstream, $result);
        }

        fclose($outstream);
    }

    public function importCustomers(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'file_customer' => 'required|mimes:csv,txt'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors('Format is incorrect, please use correct format.');
        }

        $originalFileName = $request->file('file_customer')->getClientOriginalName();
        $request->file('file_customer')->move(storage_path(), $originalFileName);

        $result = file_get_contents(storage_path($originalFileName));
        $rows = explode(PHP_EOL, $result);

        $data = $this->generateDataImports($rows, $request->import);
        $errors = [];
        foreach ($data as $key => $customer) {
            if (isset($customer['email'])) {
                if (!empty ($customer['email']) && strpos($customer['email'], '@')) {
                    $county = $this->createCustomerCounty($customer);
                    $user = User::where('account_type', User::CUSTOMER)->where('email', $customer['email'])->first();
                    if (!empty($user)) {
                        $user->update([
                            'name' => $customer['first_name'] . ' ' . $customer['last_name'],
                        ]);
                        if ($user->mailingContact && $user->mailingContact->address) {
                            $user->mailingContact->address->update([
                                'business_name' => $customer['first_name'] . ' ' . $customer['last_name'],
                                'email' => $customer['email'],
                                'phone' => $customer['phone'],
                                'address_1' => $customer['address_1'],
                                'address_2' => $customer['address_2'],
                                'address_3' => $customer['address_3'],
                                'address_4' => $customer['address_4'],
                                'postal_code' => $customer['postal_code'],
                                'city' => $customer['city'],
                                'county_id' => $county->id,
                                'country_id' => $county->country_id
                            ]);
                        } else {
                            $new_address = $this->createCustomerAddress($customer,$county,$user);
                        }
                        continue;
                    } else {
                        $user = $this->createNewCustomer($customer);
                        $new_address = $this->createCustomerAddress($customer,$county,$user);
                        $response = $this->sendPassResetNotification($user);
                        switch ($response) {
                            case Password::RESET_LINK_SENT:
                                continue 2;
                            case Password::INVALID_USER:
                                return redirect()->back()->withErrors(['email' => trans($response)]);
                        }
                    }
                } else {
                    if (!empty($customer['email'])) {
                        array_push($errors, ['This email ' . $customer['email'] . ' is not valid email address.']);
                    } else {
                        array_push($errors, ['Email field is require for ' . $customer['first_name'] . ' ' . $customer['last_name'] . ' customer.']);
                    }
                }
            }
        }
        if (file_exists(storage_path($originalFileName))) {
            unlink(storage_path($originalFileName));
        }
        return redirect()->back()->with('message', 'Import completed successfully!');
    }

    private function createNewCustomer($customer){
        $user = User::create([
            'name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'username' => $customer['email'],
            'email' => $customer['email'],
            'account_type' => User::CUSTOMER,
        ]);

        User_Link::create([
            'user_id' => $user->id,
            'parent_id' => Auth::user()->id
        ]);

        return $user;
    }

    private function createCustomerAddress($customer,$county,$user){
        $new_address = Address::create([
            'business_name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'address_1' => $customer['address_1'],
            'address_2' => $customer['address_2'],
            'address_3' => $customer['address_3'],
            'address_4' => $customer['address_4'],
            'postal_code' => $customer['postal_code'],
            'city' => $customer['city'],
            'county_id' => $county->id,
            'country_id' => $county->country_id
        ]);
        if ($new_address->id) {
            User_Contact::create([
                'user_id' => $user->id,
                'address_id' => $new_address->id,
                'type' => User_Contact::MAILING,
            ]);
        }
        return true;
    }

    private function createCustomerCounty($customer){
        $county = Counties::where('name', $customer['county'])->first();
        if (empty($county)) {
            $country = Countries::where('name', $customer['country'])->first();
            if (empty($country)) {
                $country = Countries::create([
                    'name' => $customer['country']
                ]);
            }
            $county = Counties::create([
                'name' => $customer['county'],
                'country_id' => $country->id
            ]);
        }
        return $county;
    }

    private function sendPassResetNotification($user)
    {
        $credentials = ['email' => $user->email];
        $response = Password::sendResetLink($credentials, function (Message $message) {
            $message->subject('Reset password!');
        });
        return $response;
    }

    private function generateDataImports($rows, $type)
    {
        if ($type == 'customers') {
            $columns = [
                'first_name',
                'last_name',
                'business_name',
                'email',
                'phone',
                'address_1',
                'address_2',
                'address_3',
                'address_4',
                'county',
                'city',
                'postal_code',
                'country',
            ];
        } elseif ($type == 'transactions') {
            $columns = [
                'id',
                'user_id',
                'client_name',
                'invoice_id',
                'created_at',
                'due_at',
                'updated_at',
                'amount',
                'credit',
                'tax',
                'total',
                'tax_rate',
                'status',
                'payment_method',
                'message',
            ];
        }


        unset($rows[0]);

        $data = [];
        foreach ($rows as $key => $row) {
            $items = str_getcsv($row);

            $index = 0;
            foreach ($items as $k => $item) {
                if (!empty($columns[$index])) {
                    $data[$key][$columns[$index]] = $item;
                    $index++;
                }
            }
        }

        // dd($data);

        return $data;
    }

    public function importTransactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'file_transaction' => 'required|mimes:csv,txt'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors('Format is incorrect, please use correct format.');
        }
        $originalFileName = $request->file('file_transaction')->getClientOriginalName();
        $request->file('file_transaction')->move(storage_path(), $originalFileName);
        $result = file_get_contents(storage_path($originalFileName));
        $rows = explode(PHP_EOL, $result);

        $data = $this->generateDataImports($rows, $request->import);
        $errors = [];
        foreach ($data as $key => $transaction) {
            if (isset($transaction['id'])) {
                $customer = $this->getImportedUserID($transaction['user_id']);
                if (!$customer || !$customer->contacts[0]->address_id) {
                    if(!$customer){
                       array_push($errors, ['User with id = ' . $transaction['user_id'] . ' does\'t exists']);
                    }else{
                        array_push($errors, ['This User with id = ' . $customer->id . ' does\'t have address in our system.']);
                    }
                    continue;
                } else {
                    if ($trans = Transactions::find($transaction['id'])) {
                        array_push($errors, ['Transaction id= ' . $transaction['id'] . ' already exists']);
                        continue;
                    } else {
                        if (!$invoice = Invoice::find($transaction['invoice_id'])) {
                            switch (strtoupper($transaction['status'])) {
                                case 'PAID':
                                    $invoice_status = Invoice::PAID;
                                    break;
                                case 'OVERDUE':
                                    $invoice_status = Invoice::OVERDUE;
                                    break;
                                case 'REFUNDED':
                                    $invoice_status = Invoice::REFUNDED;
                                    break;
                                case 'CANCELED':
                                    $invoice_status = Invoice::CANCELED;
                                    break;
                                case 'PENDING':
                                    $invoice_status = Invoice::PENDING;
                                    break;
                                default :
                                    $invoice_status = Invoice::UNPAID;
                            }
                            $invoice = Invoice::create([
                                'id' => $transaction['invoice_id'],
                                'user_id' => Auth::User()->id,
                                'customer_id' => $customer->id,
                                'currency_id' => !empty($user_currency = User_Setting::where([['user_id',$customer->id],['name','site.defaultCurrency']])->first()) ? $user_currency->value : 4 ,
                                'total' => $transaction['total'] ? $transaction['total'] : 0,
                                'due_at' => $transaction['due_at'] ? Carbon::parse($transaction['due_at']) : Carbon::now(),
                                'updated_at' => $transaction['updated_at'] ? $transaction['updated_at'] : null,
                                'tax' => $transaction['tax'] ? $transaction['tax'] : null,
                                'status' => $invoice_status,
                                'address_id' => $customer->contacts[0]->address_id
                            ]);
                        }
                        $trans = Transactions::create([
                            'id' => $transaction['id'],
                            'invoice_id' => $transaction['invoice_id'],
                            'user_id' => Auth::User()->id,
                            'customer_id' => $customer->id,
                            'currency_id' => !empty($user_currency = User_Setting::where([['user_id',$customer->id],['name','site.defaultCurrency']])->first()) ? $user_currency->value : 4 ,
                            'amount' => $transaction['amount'] ? $transaction['amount'] : 0,
                            'payment_method' => $transaction['payment_method'] ? $transaction['payment_method'] : null,
                            'message' => $transaction['message'] ? $transaction['message'] : '',
                            'created_at' => $transaction['created_at'] ? Carbon::parse($transaction['created_at']) : Carbon::now()
                        ]);
                    }
                }
            }
        }
        if (file_exists(storage_path($originalFileName))) {
            unlink(storage_path($originalFileName));
        }
        return redirect()->back()->with('message', 'Import completed successfully!')->withErrors($errors);

    }

    public function getImportedUserID($user_id)
    {
        if (is_numeric($user_id)) {
            $id = (int)$user_id;
            $user = User::find($id);

        } elseif (strpos($user_id, '@')) {
            $user = User::with('contacts','settings')->where('email', $user_id)->first();
        }
        if ($user) {
            return $user;
        } else {
            return false;
        }
    }

    public function exportTransactions(Request $request)
    {
        \Debugbar::disable();
        $transactions = \App\Transactions::where('created_at', '>=', $request->get('from'))
            // ->where('created_at', '<=', $request->get('to'))
            ->with('invoice.address')
            ->with('user')
            // ->where('json_response', '<>', '')
            ->get();

        if ($transactions->count() == 0) {
            return redirect()->back()->with([
                'transactions-massage' => 'No user during this period!'
            ])->withInput($request->all());
        }

        // dd(
        //     $transactions->first()->invoice->customer_id

        //     ->toArray()
        // );

        $csvExporter = new Export();

        $csvExporter->beforeEach(function ($transaction) {
            $transaction->id = $transaction->id;
            $transaction->user_id = $transaction->invoice ? $transaction->invoice->customer_id : null;
            $transaction->client_name = $transaction->invoice && $transaction->invoice->address ? $transaction->invoice->address->contact_name : null;

            $transaction->due_at = $transaction->invoice ? $transaction->invoice->due_at : null;
            $transaction->updated_at = $transaction->invoice && $transaction->invoice->status == 1 ? $transaction->invoice->updated_at : null;

            $transaction->credit = $transaction->getCredit();

            $transaction->total = $transaction->invoice ? $transaction->invoice->total : null;
            $transaction->tax = $transaction->invoice ? $transaction->invoice->tax : null;

            $transaction->tax_rate = $transaction->user && $transaction->user->taxClass && $transaction->user->taxClass->taxRate ? $transaction->user->taxClass->taxRate->rate : null;

            $transaction->status = $transaction->invoice ? $transaction->invoice->status() : null;
            $transaction->payment_method = $transaction->payment_method();
            $transaction->notes = $transaction->message;
        });

        // Redirect output to a client’s web browser (Excel5)
        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="export.csv"');
        header('Cache-Control: max-age=0');

        $text = $csvExporter->build($transactions, [
            'id' => 'ID',
            'user_id' => 'User ID',
            'client_name' => 'Client Name',
            'invoice_id' => 'Invoice Number',
            'created_at' => 'Creation Date',
            'due_at' => 'Due Date',
            'updated_at' => 'Date Paid',
            'amount' => 'Subtotal',
            'credit' => 'Credit',
            'tax' => 'Tax',
            'tax' => 'Tax2',
            'total' => 'Total',
            'tax_rate' => 'Tax_rate Rate',
            'tax_rate' => 'Tax Rate2',
            'status' => 'Status',
            'payment_method' => 'Payment Method',
            'notes' => 'Notes',
        ])->getCsv();

        return strip_tags(html_entity_decode($text));
    }
}
