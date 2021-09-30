<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Illuminate\Http\Request;
use Input;
use Invoices;
use Mail;
use Permissions;
use Response;
use Route;
use Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PdfTrait;
use App\Address;
use App\Currency;
use App\Countries;
use App\Counties;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceTotal;
use App\Order_Options;
use App\User;
use App\User_Link;
use App\User_Contact;
use App\TaxClasses;
use App\TaxRates;
use App\Mail\GeneralEmail;
use App\Mail\InvoiceEmail;

class InvoicesController extends Controller
{
	use PdfTrait;

	private $invoiceTypes = [
		'unpaid',
		'overdue',
		'paid',
		'refunded',
		'canceled',
		'billingserv'
	];

	public function __construct()
	{
		if (!Permissions::has('invoices')) {
			//throw new Permissions::$exception;
			//return redirect(route('login'));
		}
	}

	public function index($type = 'all')
	{
		if (!in_array($type, $this->invoiceTypes)) {
			$type = 'all';
		}

		Route::current()->setUri("admin/invoices/view/{{$type}?}");

		$invoices = [];
		if ($type === 'all') {
			$invoices = Invoice::where('user_id', Auth::User()->parent->first()->id)->get();
		} else {
			$invoices = Invoice::where('user_id', Auth::User()->parent->first()->id)
				->where('status', constant('App\Invoice::' . strtoupper($type)))
				->get();
		}

		$data = [
			'type' => $type,
			'invoices' => $invoices
		];

		return view('Invoices.invoicesList', $data);
	}

	public function list(Request $request)
	{
		$this->validate($request, [
			'invoice_type' => 'required',
		]);

		$userId = Auth::User()->parent->first()->id;
		$type = $request->invoice_type;

		if (!in_array($request->invoice_type, $this->invoiceTypes)) {
			$type = 'all';
		}

		$start = $request->input('start');
		$length = $request->input('length');
		$search = $request->input('search.value');

		switch($request->order[0]['column']) {
			case '0':
				$orderBy = 'invoiceNumber';
			break;
			case '1':
				$orderBy = 'contactName';
			break;
			case '2':
				$orderBy = 'package_trial';
			break;
			case '3':
				$orderBy = 'invoiceTotal';
			break;
			case '4':
				$orderBy = 'invoiceStatus';
			break;
			case '5':
				$orderBy = 'invoiceCreatedAt';
			break;
			case '6':
				$orderBy = 'invoiceDueAt';
			break;
			case '7':
				$orderBy = 'tools';
			break;
		}

		$orderDirection = $request->input('order')[0]['dir'];

		$invoiceCount = Invoice::where('user_id', $userId)
		->join(
			'users','invoices.customer_id','=',
			'users.id'
		)->join(
			'addresses',
			'invoices.address_id','=',
			'addresses.id'
		)->when($type !== 'all', function ($query) use ($type) {
			return $query->where(
				'status', constant('App\Invoice::' . strtoupper($type))
			);
		})->whereNull(
			'users.deleted_at'
		)->whereNull(
			'invoices.deleted_at'
		)->count();

		$invoiceFilteredCount = Invoice::where('user_id', $userId)
		->join(
			'users',
			'invoices.customer_id','=',
			'users.id'
		)->join(
			'addresses',
			'invoices.address_id','=',
			'addresses.id'
		)->when($type !== 'all', function ($query) use ($type) {
			return $query->where(
				'status', constant('App\Invoice::' . strtoupper($type))
			);
		})->when(!empty($search),function($query) use ($search) {
			return $query->where(
				'invoices.invoice_number','LIKE','%'.$search.'%'
			)->orWhere(
				'addresses.contact_name','LIKE','%'.$search.'%'
			)->orWhere(
				'invoices.total','LIKE','%'.$search.'%'
			)->orWhere(
				'invoices.status','LIKE','%'.$search.'%'
			)->orWhere(
				'invoices.created_at','LIKE','%'.$search.'%'
			)->orWhere(
				'invoices.due_at','LIKE','%'.$search.'%'
			);
		})->whereNull(
			'users.deleted_at'
		)->whereNull(
			'invoices.deleted_at'
		)->count();

		$invoices = Invoice::select([
			'invoices.id as invoiceId',
			'invoices.invoice_number as invoiceNumber',
			'invoices.customer_id as customerId',
			'users.name as contactName',
			'invoices.total as invoiceTotal',
			'invoices.status as invoiceStatus',
			'invoices.created_at as invoiceCreatedAt',
			'invoices.due_at as invoiceDueAt'
		])->join(
			'users',
			'invoices.customer_id','=',
			'users.id'
		)->join(
			'addresses',
			'invoices.address_id','=',
			'addresses.id'
		)->where(
			'invoices.user_id', $userId
		)->whereNull(
			'users.deleted_at'
		)->whereNull(
			'invoices.deleted_at'
		)->when($type !== 'all', function ($query) use ($type) {
			return $query->where(
				'invoices.status', constant('App\Invoice::' . strtoupper($type))
			);
		})->when(!empty($search),function($query) use ($search) {
			return $query->where(
				'invoices.invoice_number','LIKE','%'.$search.'%'
			)->orWhere(
				'addresses.contact_name','LIKE','%'.$search.'%'
			)->orWhere(
				'invoices.total','LIKE','%'.$search.'%'
			)->orWhere(
				'invoices.status','LIKE','%'.$search.'%'
			)->orWhere(
				'invoices.created_at','LIKE','%'.$search.'%'
			)->orWhere(
				'invoices.due_at','LIKE','%'.$search.'%'
			);
		})->limit($length)->offset($start)->orderBy($orderBy,$orderDirection)->get();

			$return = [
				'data' => [],
				'draw' => $request->input('draw'),
				'recordsTotal' => $invoiceCount,
				'recordsFiltered' => $invoiceFilteredCount
			];

			$arrInvoiceIds = [];
			foreach($invoices as $k=>$invoice)
			{
				switch($invoice->invoiceStatus)
				{
					case Invoice::UNPAID:
						$status = 'Unpaid';
					break;
					case Invoice::PAID:
						$status = 'Paid';
					break;
					case Invoice::OVERDUE:
						$status = 'Late';
					break;
					case Invoice::REFUNDED:
						$status = 'Refunded';
					break;
					case Invoice::CANCELED:
						$status = 'Cancelled';
					break;
					case Invoice::PENDING:
						$status = 'Pending';
					break;
				}

				if($invoice->package_trial > 0) {
					$trialPackage = '<span class="label label-warning">'.$invoice->package_trial.' day </span>';
				} else {
					$trialPackage = '<span class="label label-success">No</span>';
				}

				$return['data'][] = [
					'<a href="/admin/invoices/'.$invoice->invoiceId.'">'.$invoice->invoiceId.'</a>',
					'<a href="/customers/'.$invoice->customerId.'">'.$invoice->contactName.'</a>',
					$trialPackage,
					number_format($invoice->invoiceTotal, 2),
					$status,
					date('d/m/Y', strtotime($invoice->invoiceCreatedAt)),
					date('d/m/Y', strtotime($invoice->invoiceDueAt)),
					'<td class="tools"><a href="/admin/invoices/'.$invoice->invoiceId.'/edit"><i class="fas fa-pencil-alt"></i></a> <a href="#" class="delete" data-invoice="'.$invoice->invoiceId.'"><i class="fas fa-trash"></i></a></td>'
				];
			}

			return Response::json($return);
	}

	public function show($id)
	{
		return $this->getInvoice($id);
	}

	public function getInvoice($id, $render = false, $dataOnly = false)
	{
			$invoice = Invoice::findOrFail($id);

			if($invoice->user_id !== Auth::User()->parent->first()->id)
			{
				//NOT THE ADMIN
				return redirect(route('login'));
			}

			if(Auth::User()->isCustomer() && $invoice->customer_id !==  Auth::User()->id)
			{
				//NOT THE OWNER
				return redirect(route('login'));
			}

			$totalOptionCost = 0;
			$invoice->subtotal = 0.00;
			if ($invoice->items !== null) {
				foreach ($invoice->items as $item) {
					$totalItemCost = 0;
					$invoice->subtotal += ($item->price * $item->quantity);
				}
			}

			if ($render) {
				return $this->generatePdfFile($invoice);
			}

			//Calculate Options
			$options = $this->getOrderOptions($invoice);

			for($x = 0; $x < count($options); $x++) {
					$option = \App\Package_Options::where('id', $options[$x]['option_id'])
																					->first();
					if($option->type == 2) {
						$totalItemCost += ((int)$options[$x]['value'] * $options[$x]['price']) + $options[$x]['fee'];
					}
					else {
						$totalItemCost += $options[$x]['price'] + $options[$x]['fee'];
					}
			}
			$totalOptionCost += $totalItemCost;
			$invoice->subtotal += $totalOptionCost;
            $invoice->leftToPay = $invoice->subtotal;

            if ((int)$invoice->status === 0) {
                $transactions = $invoice->transactions;

                if ($transactions) {
                    $invoice->leftToPay -= $transactions->pluck('amount')->sum();
                }
            }

			if($dataOnly)
			{
					return $invoice;
			}

			return view('Invoices.viewInvoice', [
				'invoice' => $invoice,
				'default_currency' => Controller::setDefaultCurrency(),
				'currency' => Currency::findOrFail($invoice->currency_id),
				'options' => $options
			]);
	}

	public function renderInvoicePdf($id)
	{
		return $this->getInvoice($id, true);
	}

	public function create($customer_id = null)
	{
		$user = Auth::User();

		if (($user->isAdmin() || $user->isClient()) && $user->mailingContact === null) {
			return redirect('/settings/my-account')
				->withErrors(['Before an invoice can be created a mailing address needs to be set.']);
		} elseif ($user->isStaff()) {
		    $user = $user->parent->first();
        }

		$next = DB::table('invoices')
			->where('user_id', $user->id)
			->max('invoice_number') + 1;

		if ($next < Settings::get('invoice.startNumber', 0)) {
			$next = Settings::get('invoice.startNumber', 0);
		}

		$invoice = new Invoice();
		$invoice->user_id = $user->id;
		$invoice->invoice_number = $next;
		$invoice->due_at = date('Y-m-d', strtotime('+' . $user->getSetting('invoice.paymentsDue', 0) . ' days'));

		if (isset($customer_id)) {
			$invoice->customer_id = $customer_id;
			$invoice->address = Address::join('user_contacts', 'addresses.id', '=', 'address_id')
				->where('type', 2)
                ->where('user_id', $customer_id)->first();
		}

		//this may be way too inefficient
		$jsonCustomers = User_Contact::select('users.id','users.name','users.email','addresses.id as address_id','addresses.address_1','addresses.address_2','addresses.postal_code','addresses.city','counties.id as county_id','counties.name as county_name','countries.id as country_id','countries.name as country_name')->join('users','user_contacts.user_id','=','users.id')->join('addresses','addresses.id','=','user_contacts.address_id')->join('main.counties','counties.id','=','addresses.county_id')->join('main.countries','countries.id','=','addresses.country_id')->where('users.account_type',User::CUSTOMER)->whereNull('users.deleted_at')->whereIn('user_contacts.type',[User_Contact::MAILING,User_Contact::BILLING])->orderBy('user_contacts.id','desc')->groupBy('user_contacts.user_id')->get();

		$data = [
			'invoice' => $invoice,
			'countries' => Countries::orderByRaw('case when id IN(222,223) then -1 else id end,id')->get(),
			'funcCountyName' => function($countyId){
				return Counties::findOrFail($countyId)->name;
			},
			'taxClasses' => TaxClasses::where('user_id','=',$user->id)->get(),
			'prefix' => Settings::get('invoice.prfix'),
			'currency' => Currency::findOrFail($user->getSetting('site.defaultCurrency', 4))->symbol,
			'jsonCustomers' => $jsonCustomers
		];

		return view('Invoices.invoiceForm', $data);
	}

	public function postTaxRates(Request $request)
	{
		$user = Auth::User()->id;
		$county = $request->input('countyId');
		$arrRates = TaxRates::join('taxZones',function($join) use ($user){
			$join->on('taxRates.zone_id','=','taxZones.id')->where('taxZones.user_id','=',$user);
		})->join('taxZoneCounties',function($join) use ($county){
			$join->on('taxZoneCounties.zone_id','=','taxZones.id')->where('taxZoneCounties.county_id','=',$county);
		})->get();
		$arrRates2 = [];
		foreach($arrRates as $k=>$v)
		{
			$arrRates2[$v->class_id] = $v;
		}
		return json_encode($arrRates2);
	}

	public function edit($id)
	{
		$invoice = Invoice::findOrFail($id);
		if ($invoice->user_id !== Auth::User()->id) {
			return back()->withErrors(['An invalid invoice was selected']);
		}
		$jsonCustomers = User_Contact::select('users.id','users.name','users.email','addresses.id as address_id','addresses.address_1','addresses.address_2','addresses.postal_code','addresses.city','counties.id as county_id','counties.name as county_name','countries.id as country_id','countries.name as country_name')->join('users','user_contacts.user_id','=','users.id')->join('addresses','addresses.id','=','user_contacts.address_id')->join('main.counties','counties.id','=','addresses.county_id')->join('main.countries','countries.id','=','addresses.country_id')->where('users.account_type',User::CUSTOMER)->whereNull('users.deleted_at')->whereIn('user_contacts.type',[User_Contact::MAILING,User_Contact::BILLING])->orderBy('user_contacts.id','desc')->groupBy('user_contacts.user_id')->get();

		return view('Invoices.invoiceForm', [
			'invoice' => $invoice,
			'countries' => Countries::all(),
			'taxClasses' => TaxClasses::where('user_id','=', Auth::User()->id)->get(),
			'jsonCustomers' => $jsonCustomers,
			'currency' => Currency::findOrFail(Auth::User()->getSetting('site.defaultCurrency', 4))->symbol
		]);
	}

    public function store(Request $request)
    {
        $this->validate($request, [
            'customer.name'        => 'required_without:customer.id',
            'customer.email'       => 'required_without:customer.id',
            'customer.postal_code' => 'required_without:customer.id',
            'duedate'              => 'required|date',
            'invoiceNumber'        => 'required|numeric|unique:invoices,invoice_number',
            'record'               => 'required|array'
        ]);

        $user = Auth::User();
        if ($user->isStaff()) {
            $user = $user->parent->first();
        }

        $customer_id = $address_id = '';

        if ($request->has('customer.id')) {
            $customer_id = $request->input('customer.id');
        }

        if ($customer_id !== 'new' && !empty($customer_id)) {
            try {
                $customer = $user->customers->find($request->input('customer.id'));
                $customer_id = $customer->id;
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['An invalid customer was selected.']);
            }
        } else {
            $test = User::where('email', $request->input('customer.email'))->first();
            if (empty($test)) {
                $customer = new User();
                $customer->name = $request->input('customer.name');
                $customer->username = $request->input('customer.email');
                $customer->email = $request->input('customer.email');
                $customer->account_type = User::CUSTOMER;
                $customer->save();
            } else {
                if ($test->account_type === User::CUSTOMER) {
                    $customer = $test;
                } else {
                    return back()->withInput()->withErrors(['That email address is already in use by a non-customer.']);
                }
            }
        }

        $customer_id = $customer->id;

        if ($request->has('customer.address.id')) {
            $address_id = $request->input('customer.address.id');
            if (!empty($address_id) && $address_id !== 'new') {
                $address = Address::find($address_id);
                if (empty($address)) {
                    $address_id = 'new';
                } else {
                    if ($address->address_1 !== $request->input('customer.address_1')) {
                        $address_id = 'new';
                    } elseif ($address->address_2 !== $request->input('customer.address_2')) {
                        $address_id = 'new';
                    } elseif ($address->city !== $request->input('customer.city')) {
                        $address_id = 'new';
                    } elseif ($address->county_id !== $request->input('customer.county')) {
                        $address_id = 'new';
                    } elseif ($address->country_id !== $request->input('customer.country')) {
                        $address_id = 'new';
                    } elseif ($address->postal_code !== $request->input('customer.postal_code')) {
                        $address_id = 'new';
                    }
                }
            }
        }

        if ($address_id === 'new') {
            $address = new Address();
            $address->contact_name = $customer->name;
            $address->email = $customer->email;
            $address->address_1 = $request->input('customer.address_1');
            $address->address_2 = $request->input('customer.address_2');
            $address->city = $request->input('customer.city');
            $address->county_id = $request->input('customer.county');
            $address->country_id = $request->input('customer.country');
            $address->postal_code = $request->input('customer.postal_code');
            $address->save();

            $address_id = $address->id;

            $customer->mailingContact()->create([
                'type'        => \App\User_Contact::MAILING,
                'address_id'  => $address->id,
                'customer_id' => $customer->id
            ]);

            $userLink = new User_Link();
            $userLink->user_id = $customer->id;
            $userLink->parent_id = $user->id;
            $userLink->save();
        }

        if (!isset($address) || empty($address)) {
            throw new \Exception('No address provided');
        }

        $dueDate = $request->input('duedate');

        //not sure why this isnt working sometimes
        //$invoice = Invoices::create($user->id, $customer_id, $dueDate, $request->input('invoiceNumber'), false, $request->input('estimate') === 'Y');

        $invoice_number = $request->input('invoiceNumber');
        if (!$invoice_number) {
            $invoice_number = DB::table('invoices')
                    ->where('user_id', $user->id)
                    ->max('invoice_number') + 1;

            if ($invoice_number < $user->getSetting('invoice.startNumber', 0)) {
                $invoice_number = $user->getSetting('invoice.startNumber', 0);
            }
        }

        if (!isset($status)) {
            $status = Invoice::UNPAID;
        }

        $invoice = new Invoice();
        $invoice->user_id = $user->id;
        $invoice->customer_id = $customer_id;
        $invoice->address_id = $address->id;
        $invoice->currency_id = $currency = $user->getSetting('site.defaultCurrency', 4);
        $invoice->invoice_number = $invoice_number;
        $invoice->status = $status;
        $invoice->due_at = $dueDate;
        $invoice->estimate = $request->input('estimate') === 'Y';
        $invoice->comments = $request->input('comments');
        $invoice->save();

        $tax = 0;
        $total = 0;
        session()->put('_session_record', json_encode($request->all()));
        if ($request->has('record')) {
            foreach ($request->input('record') as $record) {

                if ($record === "price" && (int) $record === 0) {
                    continue;
                }

                try {

                    $item = new InvoiceItem();
                    $item->invoice_id = $invoice->id;
                    $item->item = $record['item'];
                    $item->description = $record['description'];
                    $item->price = $record['price'];
                    $item->quantity = $record['quantity'];
                    $item->tax_class = $record['taxclass'];
                    $item->tax = $record['tax'];

                    $item->save();
                } catch (\Exception $exception) {
                    session()->put('_err', $exception->getMessage());
                }
                if ($record['tax'] != '')
                    $tax = $tax + $record['tax'];
                if ($record['price'] != '' && $record['quantity'])
                    $total = $total + ($record['price'] * $record['quantity']);
            }
        }

        if ($request->has('subtotal')) {
            foreach ($request->input('subtotal') as $k => $subtotal) {
                // Prevent empty subtotals from creating an invoice_total.
                if ($subtotal === "price" && (int) $subtotal === 0) {
                    continue;
                }

                if ($k == 'tax') {
                    $tax = $subtotal['price'];
                } else {
                    try {
                        $invoiceTotal = new InvoiceTotal();
                        $invoiceTotal->invoice_id = $invoice->id;
                        $invoiceTotal->item = $subtotal['item'];
                        $invoiceTotal->price = $subtotal['price'];
                        $invoiceTotal->taxclass = $subtotal['taxclass'];
                        $invoiceTotal->tax = $request->get('subtotal')['tax']['price'];
                        $invoiceTotal->save();
                    } catch (\Exception $exception) {
                        session()->put('_err', $exception->getMessage());
                    }

                    $total = $total + $subtotal['price'] + $tax;
                }
            }
        }

        $invoice->total = $total;
        $invoice->tax = $tax;
        $invoice->save();

        Invoices::sendEmail($invoice);

        return Redirect('/admin/invoices')->with('success', 'Invoice created successfully.');
    }

	public function update($id, Request $request)
	{
		$this->validate($request, [
			'duedate'              => 'required|date',
			'status'              => 'required|numeric',
			'record'               => 'required|array'
		]);

		try {
			$invoice = Invoice::findOrFail($id);
			if ($invoice->user_id !== Auth::User()->id) {
				throw new \Exception('Not the owner of this invoice.');
			}

			if($request->input('status') < 6)
			{
				$invoice->status = $request->input('status');
			}

			$invoice->estimate = $request->input('estimate') == 'Y';
			$invoice->comments = $request->input('comments');
			$invoice->due_at = $request->input('duedate');
			$invoice->invoice_number = $request->input('invoiceNumber');

			$price = 0.00;

			$invoice->items()->delete();
			if ($request->has('record')) {
				foreach ($request->input('record') as $record) {
					// Prevent empty records from creating an invoice_item.
					if (count(array_flip($record)) === 1) {
						continue;
					}

					$invoice->items()->create([
						'invoice_id' => $invoice->id,
						'item' => $record['item'],
						'description' => $record['description'],
						'price' => $record['price'],
						'quantity'=> $record['quantity']
					]);

					$price += floatval($record['price'] * $record['quantity']);
				}
			}

			$invoice->totals()->delete();
			if ($request->has('subtotal')) {
				foreach ($request->input('subtotal') as $subtotal) {
					// Prevent empty subtotals from creating an invoice_total.
					if (count(array_flip($subtotal)) === 1) {
						continue;
					}

					$invoice->totals()->create([
						'invoice_id' => $invoice->id,
						'item' => $subtotal['item'],
						'price' => $subtotal['price']
					]);

					$price += floatval($subtotal['price']);
				}
			}

			$invoice->total = $price;
			$invoice->save();

			return Redirect('/admin/invoices')->with('success', 'Invoice updated successfully.');
		} catch (\Exception $e) {
			return back()->withInput()->withErrors([$e->getMessage()]);
		}
	}

	public function destroy($id)
	{
		$invoice = Invoice::findOrFail($id);
		if ($invoice->user_id !== Auth::User()->id) {
			throw new \Exception('Not the owner of this invoice.');
		}

		$invoice->forceDelete();

		return 1;
	}

	private function getOrderOptions($invoice)
	{
			$orderOptions = Order_Options::where('order_id', $invoice->order_id)
																		->orderBy('id', 'asc')
																		->get();

			$options = [];
			foreach ($orderOptions as $key => $value) {
				$optionsItem['id'] = $value->option_value->id;
				$optionsItem['option_id'] = $value->option_value->option_id;
				$optionsItem['display_name'] = $value->option_value->display_name;
				$optionsItem['price'] = $value->amount;
				$optionsItem['fee'] = $value->option_value->fee;
				$optionsItem['cycle_type'] = $value->cycle_type;
				$optionsItem['value'] = $value->value;

				$options[] = $optionsItem;
			}
			return $options;
	}

	public function sendInvoiceEmail($id)
	{
	    $user = auth()->user();
	    if ($user->isStaff()) {
	        $user = $user->parent->first();
        }

        $invoice = $this->getInvoice($id, false, true);
        $userFrom = $user;
        $customer = $invoice->customer;
        $subject = $user->siteSettings('name') . ' Invoice #' . $user->getSetting('invoice.prefix','') . $invoice->invoice_number;
        $content = [
            'user' => $user,
            'customer' => $customer,
            'invoice' => $invoice,
            'currency' => Controller::setDefaultCurrency(),
            'subTotal' => $invoice->subtotal,
            'address' => Address::findOrFail($invoice->address_id)
        ];
        $view = 'Invoices.invoiceReceiptEmail';
        Mail::to($customer)->send(new InvoiceEmail($userFrom->email, $userFrom->name, $subject, $content, $view));

        return redirect()->back()->with('status', 'Invoice sent.');
	}
}
