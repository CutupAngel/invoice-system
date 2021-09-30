<?php

namespace App\Http\Controllers;

use App\Mail\AdminOrderNotification;
use App\Mail\OrderNotificationEmail;
use Auth;
use DB;
use Illuminate\Support\Facades\Cache;
use Input;
use Integrations;
use Response;
use Mail;
use Session;
use Settings;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\StripeClient;
use Stripe\Exception\CardException;

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CheckoutIntegrationsController;
use Illuminate\Http\Request;

use App\Discount;
use App\Order;
use App\Order_Options;
use App\Order_Settings;
use App\Address;
use App\Countries;
use App\Counties;
use App\Currency;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceTotal;
use App\User;
use App\User_Link;
use App\Package;
use App\PackageSetting;
use App\Integration;
use App\IntegrationCpanel;
use App\Transactions;
use App\User_Contact;
use App\TaxRates;

/**
 * New Bascket cart
 */
use Cart;
use Validator;
use App\Mail\GeneralEmail;
use App\Mail\InvoiceEmail;
use App\Http\Controllers\CommonController;
use App\Packages\APIs\Payflow;

use PH7\Eu\Vat\Validator as VatValidator;
use PH7\Eu\Vat\Provider\Europa;

use FraudLabsPro\Configuration as FraudConfiguration;
use FraudLabsPro\Order as FraudOrder;
use Cardinity\Method\Payment\Finalize;
use Cardinity\Client;
use Cardinity\Exception\InvalidAttributeValue;
use Cardinity\Exception\ValidationFailed;
use Cardinity\Exception\Declined;
use Cardinity\Exception\NotFound;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckoutController extends Controller
{
	/**
	 * @var $user
	 */
	public $user = null;

	/**
	 * @var $currency
	 */
	private $currency = null;

	/**
	 * @var $defaultCurrency
	 */
	private $defaultCurrency = null;

	public function __construct()
	{
		$this->currency = Controller::setCurrency();
		$this->user = Auth::user();
	}

	public function formedUserTemplate()
	{
		return [
			'checkout2',
			auth()->user(),
		];

		if (session()->has('cart.tokenAuthed')) {
			$invoice = Invoice::findOrFail(session()->get('cart.inv'));

			session()->put('cart.type', 2);

			return [
				'checkout2HashedIn',
				$invoice->customer,
			];
		} elseif ($this->user && $this->user->account_type == 2) {

			session()->put('cart.type', 1);

			return [
				'checkout2LoggedIn',
				$this->user,
			];
		} elseif ($this->user && $this->user->account_type == 1) {

			session()->put('cart.type', 1);

			return [
				'checkout2LoggedIn',
				$this->user,
			];
		} elseif ($this->user && $this->user->account_type == 0) {
			session()->put('cart.type', 1);

			return [
				'checkout2LoggedIn',
				$this->user,
			];
		}

		Session::put('url.intended', '/checkout');
		session()->put('cart.type', 0);

		return [
			'checkout2',
			null,
		];
	}

	/**
	 * Medthod for formed checkout page view
	 */
	public function getCheckout()
	{
		/**
		 * Get bascket cart info
		 */

		$bascket = CommonController::factory()->registerBasketInfo();

		session()->put('_basket', $bascket);

		/**
		 * Redirect user with empty bascket to home page
		 */
		if ($bascket['basketCount'] <= 0) {
			return redirect()->to('home');
		}

		$package_id = '';
		foreach($bascket['basketItems'] as $items)
		{
			$package_id = $items->id;
			break;
		}

		$countries = Countries::orderByRaw('case when id IN(222,223) then -1 else id end,id')
			->get();

		/**
		 * Get customer and template
		 */
		list($template, $customer) = $this->formedUserTemplate();

		/**
		 * Get cart content
		 */
		$cart = Controller::formatCartData();

		$gateway = new PaymentController();

		$gateway->user = User::find(1); //$this->user;
		$gateway->customer = $customer;
		$paymentMethods = $gateway->getAvailablePaymentMethods();
		$tokenPayment = $gateway->getGatewayTokenStatus();
		$savedPayments = $gateway->getUsersSavedPaymentMethods();

		$discountCode = session()->has('cart.discountCode') ? session()->get('cart.discountCode') : '';

		$grandTotal = session()->get('cart.grandTotal');

		$fixedDiscountPercentage = Discount::where('user_id', Controller::site('id'))
											->where('type', '') //Discount::FIXED)
											->where('value', '<=', $grandTotal)
											->where('start','<=', date('Y-m-d h:m:s'))
											->where(function($query){
															$query->where('end','0000-00-00')
															->orWhere('end','>=', date('Y-m-d h:m:s'));
														})
											->orderBy('value', 'desc')
											->first();

		if($fixedDiscountPercentage && Settings::get('invoice.fixedDiscount')) {
			$fixedDiscountPercentage = $fixedDiscountPercentage->discount;
			session()->put('cart.fixedDiscountPercentage', $fixedDiscountPercentage);
		}
		else {
			$fixedDiscount = 0;
			$fixedDiscountPercentage = 0;
			session()->put('cart.fixedDiscountPercentage', 0);
		}

		$data = [
			'discountCodesExist' => Discount::factory()->getDiscountCount($this->user),
			'discountCode' => $discountCode,
			'fixedDiscountPercentage' => $fixedDiscountPercentage,
			'countries' => $countries,
			'user'  => $this->user,
			'contact' => $this->getContactInfo(),
			'customer'  => $customer,
			'tokenPayment' => $tokenPayment[0],
			'cart' => $cart,
			'currency'=>$this->currency,
			'default_currency' => Controller::setDefaultCurrency(),
			'subTotal'=>session()->get('cart.subTotal'),
			'savedPayments'=>$savedPayments[0],
			'savePaymentsAllowed'=>$savedPayments[1],
			'tax'=>0,
			'paymentNames' => [
				'tokenDataCCType' => 'name="paymentMethod[cctype]"',
				'tokenDataCCNumber' => 'name="paymentMethod[number]"',
				'tokenDataCCCvc' => 'name="paymentMethod[cvc]"',
				'tokenDataExpMonth' => 'name="paymentMethod[expiration][month]"',
				'tokenDataExpYear' => 'name="paymentMethod[expiration][year]"',
				'tokenDataCCName' => 'name="paymentMethod[cardname]"'
			],
			'paymentMethods' => $paymentMethods,
			'gateway' => $gateway,
			'package_id' => $package_id
		];

		if(Cart::tax() == '0.00')
		{
				$bascket['basketGrendTotal'] = $bascket['basketGrendTotal'] - $bascket['totalTax'];
				$bascket['basketGrendTotalNormal'] = $bascket['basketGrendTotalNormal'] - $bascket['totalTaxNormal'];
		}

		$content = Cart::content();
		$taxRate = 0;
		$optionTax = 0;
		foreach($content as $contentItem)
		{
				$taxRate = $contentItem->taxRate;

				if(isset($item->options->options))
				{
						foreach($contentItem->options->options as $option)
						{
								$optionTax += $option['price'] + $option['fee'];
						}
				}
		}
		
		$tax_cart = Cart::tax();

		$tax_cart = str_replace(",", "", $tax_cart);
		
		

		$totalOptionTax = $optionTax * ($taxRate / 100);
		foreach($bascket['taxes'] as $bascketTaxes => $val)
		{
				if(Cart::tax() == '0.00') $bascket['taxes'][$bascketTaxes]['name'] = Cart::tax();
				else $bascket['taxes'][$bascketTaxes]['name'] = $taxRate;
				
				$bascket['taxes'][$bascketTaxes]['tax'] = $tax_cart + $totalOptionTax;
				$bascket['totalTax'] = $tax_cart + $totalOptionTax;
				$bascket['totalTaxNormal'] = $tax_cart + $totalOptionTax;
				$bascket['basketGrendTotal'] = $bascket['basketTotal'] + $bascket['totalTax'];
				$bascket['basketGrendTotalNormal'] = $bascket['basketTotalNormal'] + $bascket['totalTaxNormal'];
		}
		
		

		$data = array_merge($data, $bascket);
		
		if (!is_null($tokenPayment[1])) {
			$data = array_merge($data, $tokenPayment[1]);
			$data['paymentNames'] = $tokenPayment[2];
		}

		$userAdmin = User::find(1);
		$data['userAdmin'] = $userAdmin;
		if(Auth::check()) {
			$data['user'] = Auth::user();
			$data['contact'] = Auth::user()->mailingContact->address;
			$split_name = Auth::user()->split_name();
			$data['firstname'] = $split_name[0];
			$data['lastname'] = $split_name[1];

			try {
				//Setup Stripe
				Stripe::setApiKey($userAdmin->getSetting('stripe.secretkey'));

				//check if has saved card
				$stripeCards = PaymentMethod::all([
											  'customer' => Auth::user()->stripeId,
											  'type' => 'card',
											]);
				$data['stripeCards'] = $stripeCards;
			}
			catch (\Exception $e) {

			}
		}
		else {
			//if bank transfer available
			$bankInformation = $userAdmin->getSetting('banktransfer.information');
			if(!is_null($bankInformation))
			{
					$data['bank_information'] = $bankInformation;
			}
		}

		//if(session()->has('cardinity_errors')) dd(session());

		return view('Checkout.'.$template, $data);
	}

	public function setAttributeContact($type)
	{
		$types = [
			'0' => 'account',
			'1' => 'mailing',
			'2' => 'biling',
			'3' => 'admin',
			'4' => 'tech',
		];

		return isset($types[$type]) ? $types[$type] : null;
	}

	public function getContactInfo()
	{
		if (!$this->user) {
			return null;
		}

		$data = [];
		foreach ($this->user->contacts as $contact) {
			$data[$this->setAttributeContact($contact->type)] = $contact->address;
		}

		return (object) $data;
	}

	public function validateCheckoutForm($request, $checkoutType)
	{
		switch ($checkoutType) {
			case 0:
				$errors = $this->validateCheckout_NewUser($request);
			break;
			case 2:
				$errors = $this->validateCheckout_HashedUser_AccountInfo($request);
			break;
			case 1:
				$errors = $this->validateCheckout_LoggedInUser($request);
			break;
		}

		return array_merge($errors, $this->validateCheckout_PaymentMethod($request));
	}

	/**
	 * Method generate rules for form
	 *
	 * @return array
	 */
	public function rules(Request $request = null)
	{
		/**
		 * Get rules user account
		 */
		$useraccount = $this->rulesUserAccount();

		/**
		 * Get rules biling information
		 */
		$bilingInformation = $this->rulesBilingInformation();

		/**
		 * Get rules biling address
		 */
		$bilingAddress = $this->rulesBilingAddress();

		/**
		 * Get rules payment method
		 */
		//$payment = $this->rulesPayments();
		if($request->input('paymentMethod.type') <> '0') $payment = [];

		/**
		 * Bascket siderbar
		 */
		$siderBar = $this->rulesBascketSiderBar();

		/**
		 * Merge array variable rules
		 */
		$rules = array_filter(array_merge(
				$useraccount,
				$bilingInformation,
				$bilingAddress,
				//$payment,
				$siderBar
			)
		);

		return $rules;
	}

	public function rulesBascketSiderBar()
	{
		return [
			'term' => 'required',
		];
	}

	public function rulesPayments()
	{
		return [
			'paymentMethod.type' => 'required',
			'paymentMethod.number' => 'required|numeric',
			'paymentMethod.cardname' => 'required',
			'paymentMethod.expiration.month' => 'required|numeric',
			'paymentMethod.expiration.year' => 'required|numeric',
			'paymentMethod.cvc' => 'required|numeric',
		];
	}

	public function getAttributeNames()
	{
		return [

			/**
			 * Account
			 */
			'account.email' => 'E-mail',
			'account.username' => 'Username',
			'account.password' => 'Password',
			'account.password2' => 'Password Confirmation',

			/**
			 * Biling information
			 */
			'billingInfo.firstname' => 'First Name',
			'billingInfo.lastname' => 'Last Name',
			'billingInfo.company' => 'Company',
			'billingInfo.phone' => 'Phone',
			'billingInfo.fax' => 'Fax',
			'billingInfo.email' => 'E-mail',

			/**
			 * Addresses
			 */
			'billingAddress.address1' => 'Address',
			'billingAddress.address2' => 'Address',
			'billingAddress.address3' => 'Address',
			'billingAddress.address4' => 'Address',
			'billingAddress.city' => 'City',
			'billingAddress.region' => 'Region',
			'billingAddress.country' => 'Country',
			'billingAddress.zip' => 'Zip',

			/**
			 * Payment method
			 */
			'paymentMethod.type' => 'Type Card',
			'paymentMethod.number' => 'Card Number',
			'paymentMethod.cardname' => 'Name Om Card',
			'paymentMethod.expiration.month' => 'Month',
			'paymentMethod.expiration.year' => 'Year',
			'paymentMethod.expiration.cvc' => 'CVC Code',

			/**
			 * Bascket siderbar
			 */
			'term' => 'Agreements',
		];
	}

	public function rulesUserAccount()
	{
		$user = Auth::user();

		if ($user) {
			return [];
		}

		return [
			'account.email' => 'email|required|max:255|unique:users,email',
			//'account.username' => 'required|max:255|unique:users,username',
			'account.businessname' => 'max:255',
			'account.password' => 'required|min:4',
			'account.password_confirmation' => 'required|same:account.password',
		];
	}

	public function rulesBilingInformation()
	{
		return [
			'billingInfo.firstname' => 'required|max:255',
			'billingInfo.lastname' => 'required|max:255',
			// 'billingInfo.company' => 'required|max:255',
			'billingInfo.phone' => 'required|max:20',
			// 'billingInfo.fax' => 'required|max:20',
			'billingInfo.email' => 'required|email',
		];
	}

	public function rulesBilingAddress()
	{
		return [
			'billingAddress.address1' => 'required|max:255',
			'billingAddress.address2' => 'max:255',
			'billingAddress.address3' => 'max:255',
			'billingAddress.address4' => 'max:255',
			'billingAddress.city' => 'required|max:255',
			'billingAddress.region' => 'required|max:255',
			'billingAddress.country' => 'required|max:255',
			'billingAddress.zip' => 'required',
		];
	}

	/**
	 * Create user
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \App\User
	 */
	public function createUserAccount($request)
	{
		/**
		 * Create user
		 */
		$password = bcrypt($request->input('account.password'));

		$vatNumber = '';
		if($request->companyVat == 'yes') $vatNumber = $request->vatNumber;

		$user = User::create([
			'name' => $request->input('billingInfo.firstname').' '.$request->input('billingInfo.lastname'),
			'username' => $request->input('account.email'),
			'email' => $request->input('account.email'),
			'password' => $password,
			'account_type' => 2,
			'vat_number' => $vatNumber
		]);

		return $user;
	}

	public function getCustomer($checkoutType, $request)
	{
		/**
		 * if user is logged in via token and has supplied
		 * a username and password they wish to use
		 * EDIT: this should be changed to only work if they havent registered yet...
		 */
		if ($checkoutType == 2 && $request->has('account.username') && !empty($request->input('account.username'))) {

			$customer = User::findOrFail(session()->get('cart.tokenUser'));
			$customer->username = $request->input('account.username');
			$customer->password = bcrypt($request->input('account.password'));
			$customer->save();
		} elseif ($checkoutType == 2) {

			$customer = User::findOrFail(session()->get('cart.tokenUser'));
		} elseif ($checkoutType == 1) {
			$customer = Auth::User();
		}

		return Auth::User();
	}

	/**
	 * Process Address model
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \App\Address
	 */
	private function processAddressModel(Request $request)
	{
		return Address::create([
			'contact_name' => $request->input('billingInfo.firstname').' '.$request->input('billingInfo.lastname'),
			'phone' => ($request->input('billingInfo.phone') ? $request->input('billingInfo.phone') : ''),
			'business_name' => $request->input('account.businessname'),
			'address_1' => $request->input('billingAddress.address1'),
			'address_2' => ($request->input('billingAddress.address_2') ? $request->input('billingAddress.address_2') : ''),
			'address_3' => ($request->input('billingAddress.address_3') ? $request->input('billingAddress.address_3') : ''),
			'address_4' => ($request->input('billingAddress.address_4') ? $request->input('billingAddress.address_4') : ''),
			'email' => $request->input('billingInfo.email'),
			'city' => $request->input('billingAddress.city'),
			'county_id' => $request->input('billingAddress.region'),
			'postal_code' => $request->input('billingAddress.zip'),
			'country_id' => $request->input('billingAddress.country'),
		]);
	}

	/**
	 * Function for get messages by columns rules
	 *
	 * @return array
	 */
	private function customMessages()
	{
		return [
			'account.username.unique' => 'This user already exists.',
			'account.email' => 'Email address already exists.',
		];
	}

	/**
	 * Main process checkout post request
	 *
	 * @param \Request $request
	 */
	public function postCheckout(Request $request)
	{
				$default_currency = Controller::setDefaultCurrency();
				$currency = $this->currency;
        /*
        * User Auth
        */
        $user = Auth::user();

        $validator = Validator::make(
            $request->all(),
            $this->rules($request),
            $this->customMessages(),
            $this->getAttributeNames()
        );
        /*
        * Check validator
        */
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ], 403);
        }

		
        try {
            $this->fraudLabsCheck($request);
        } catch (\Exception $e) {
            return response()->json(null, 402);
        }

        /*
    * Check if user not logged in then user autologin
    */
        if (!$user) {
            $user = $this->createUserAccount($request);
            $userlink = $this->createUserLink($user);
            Auth::login($user, true);
        }
        //return Auth::user();
        $user = Auth::user();

        //save VAT number to user
        $vatNumber = '';
        if($request->companyVat == 'yes') $vatNumber = $request->vatNumber;
        $user->vat_number = $vatNumber;
        $user->save();
				session()->put('use_tax', $request->useTax);
				session()->put('tax_amount', $request->taxAmountHidden);

        /**
         * Check if user when use credit or not
         */
        $useCredit = 0;
        $creditValue = 0;
        if (isset($request->credit)) {
            /**
             * Check user credit
             */
            $credit = \App\MiscStorage::where('name','account-credit')->where('user_id', $this->user->id)->first();
            if ($credit) {
                $creditValue = number_format(intval($credit->value), 2);
                $useCredit = 1;
            }
        }
        /*
        * User account type
        */
        $checkoutType = $user->account_type;
        /*
        * Customer is a user
        */
        $customer = $user;
        /*
         * if user is not logged in and wishes to register (currently required);
         */
        if($checkoutType == 0) {
            $customer = $this->createUserAccount($request);
            $userlink = $this->createUserLink($customer);
        }
        /*
        * Call payment gateway controller
        */
        $gateway = new PaymentController();
        $gateway->customer = $customer;

        if ($checkoutType == 0 || $checkoutType == 2 || ($checkoutType == 1 && !empty($request->billingAddress['address1']))) {
            /**
             * Create address
             */
            //check if user contact not exist, then create new address
            $user_contact = User_Contact::where('user_id', $user->id)->first();
            if (!$user_contact)	{
                $contact = $this->processAddressModel($request);
                $user_contact = $this->createUserContact($customer, $contact);
            } else {
                $contact = Address::find($user_contact->address_id);
                $contact->contact_name = $request->input('billingInfo.firstname').' '.$request->input('billingInfo.lastname');
                $contact->phone = ($request->input('billingInfo.phone') ? $request->input('billingInfo.phone') : '');
                $contact->address_1 = $request->input('billingAddress.address1');
                $contact->address_2 = ($request->input('billingAddress.address_2') ? $request->input('billingAddress.address_2') : '');
                $contact->address_3 = ($request->input('billingAddress.address_3') ? $request->input('billingAddress.address_3') : '');
                $contact->address_4 = ($request->input('billingAddress.address_4') ? $request->input('billingAddress.address_4') : '');
                $contact->email = $request->input('billingInfo.email');
                $contact->city = $request->input('billingAddress.city');
                $contact->county_id = $request->input('billingAddress.region');
                $contact->postal_code = $request->input('billingAddress.zip');
                $contact->country_id = $request->input('billingAddress.country');
                $contact->save();
            }

            $addressId = $contact->id;
            $gateway->billingAddress = $contact;

            if ($request->paymentMethod['type'] == 0) {
                $gateway->savedPaymentMethod = $gateway->getUsersSavedPaymentMethod($request->paymentMethod['type']);
            } else {
                $gateway->savedPaymentMethod = $gateway->getUsersSavedPaymentMethod($request->paymentMethod['type']);
            }
            if ($checkoutType == 0) {
                $contact2 = $this->processAddressModel($request);
                $user_contact = $this->createUserContact($customer, $contact2);
            }
        } else {
            if ($request->paymentMethod['type'] == 0) {
                $gateway->savedPaymentMethod = $gateway->getUsersSavedPaymentMethod($request->paymentMethod['type']);
                $gateway->billingAddress = $gateway->savedPaymentMethod->address;
            } elseif ($request->paymentMethod['type'] == 1) {
                $gateway->savedPaymentMethod = $gateway->getUsersSavedPaymentMethod($request->paymentMethod['type']);
                $gateway->billingAddress = $gateway->savedPaymentMethod->address;
            }
        }
        /*
        * Get taxexempt
        */
        $taxexempt = $this->getTaxexempt($request);

        /*
        * New cart
        */
        $basket = CommonController::factory()->registerBasketInfo();
        /*
        * If address ID not set
        */
        if(!isset($addressId)) {

            if (isset($gateway->billingAddress) && !empty($gateway->billingAddress)) {
                $addressId = $gateway->billingAddress->id;
            } else {
                $userAddress = User_Contact::where('user_id', $customer->id)->where('type',2)->first();
                if (!$userAddress) {
                    $addressId = $userAddress->address_id;
                    $gateway->billingAddress = Address::findOrFail($addressId);
                } else {
                    return Response::json([
                        'errors'=>  [[
                            'error_message' => 'No address id',
                            'inputs' => []
                        ]],
                        'redirect'=>false
                    ]);
                }
            }

        } // End check set address id

        /*
        * Set invoice number with check max
        */
        $next = DB::table('invoices')->where('user_id', Controller::site('id'))->max('invoice_number') + 1;
        if($next < Settings::get('invoice.startNumber', 0)) {
            $next = Settings::get('invoice.startNumber', 0);
        }
        /*
        * Try to create invoice
        */
        if($useCredit == 1) {
            if($creditValue >= $basket['basketGrendTotal']) {
                $remainingCreditValue = $creditValue - $basket['basketGrendTotal'];
                $creditValue = $creditValue - $remainingCreditValue;
                $totalPayment = 0;
                $totalPaymentNormal = 0;
            } else {
                $remainingCreditValue = 0;
                $totalPayment = $basket['basketGrendTotal'] - $creditValue;
                $totalPaymentNormal = $basket['basketGrendTotalNormal'] - $creditValue;
            }
        } else {
            $totalPayment = $basket['basketGrendTotal'];
            $totalPaymentNormal = $basket['basketGrendTotalNormal'];
        }

        //if company using VAT
        if ($request->companyVat == 'yes' && session()->get('use_tax') == 'no') {
            $totalPayment = $basket['basketGrendTotal'] - $request->taxAmountHidden;
            $totalPaymentNormal = $basket['basketGrendTotalNormal'] - $request->taxAmountHidden;
        }

        try {
            if (session()->has('cart.mode') && session()->get('cart.mode') == 'invoice') {
                $invoice = Invoice::find(session()->get('cart.inv'));
            } else {
                $invoice = new Invoice();
                $invoice->user_id = Controller::site('id');
                $invoice->customer_id = $customer->id;
                $invoice->address_id = $addressId;
                $invoice->total = $totalPayment / $default_currency->conversion * $currency->conversion;
                if($totalPayment = 0) $invoice->status = 1;
                $invoice->credit = $creditValue;
                $invoice->invoice_number = $next;
                $invoice->tax_exempt = $taxexempt;
                $invoice->due_at = date('Y-m-d');
                $invoice->currency_id = session()->has('cart.currency') ? session()->get('cart.currency') : Settings::get('site.defaultCurrency', 4);
                $invoice->save();
            }

            /*
            * Update user credit
            */
            if($useCredit == 1) {
                if(isset($credit)) {
                    $credit->value = $remainingCreditValue;
                    $credit->save();
                }
            }

        } catch (\Exception $e) {
            return Response::json([
                'errors'=>  [[
                    'error_message' => 'Error create invoice : '. $e->getMessage(),
                    'inputs' => []
                ]],
                'redirect' => false
            ]);
        }

        if (session()->has('cart.mode') && session()->get('cart.mode') == 'invoice') {
            //not create new invoice item
        } else {
            /*
            * Insert Per items
            */
            $index = 1;
            foreach ($basket['basketItems'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item' => $item->name,
                    'product' => '0',
                    'description' => '',
                    'price' => $this->getSubTotal($item),
                    'quantity' => $item->qty,
                    'package_id' => null, //$item->id,
                ]);
            }
        }

        /*
        * Insert taxes
        */
        if($basket['taxes'] && sizeof($basket['taxes']) > 0) {
            $index = 1;
			
            foreach ($basket['taxes'] as $tax) {
				$tax_ammnt = $tax['tax'];
				if(($request->input('taxAmountHidden') !== NULL)){
					$tax_ammnt = $request->input('taxAmountHidden');
				}
                InvoiceTotal::create([
                    'invoice_id' => $invoice->id,
                    'item' => $index,
                    'price' => $tax_ammnt,
                ]);
                $index++;
            }
        }
        /**
         * process paymentMethod / start payment in the case
         * of IPN stuff
         */
        $gateway->invoice = $invoice;
        $gateway->user = $invoice->user;
        $gateway->request = $request;
        $gateway->currency = $this->currency;
        $gateway->paymentMethod = $request->paymentMethod;

        try {
            $paymentStatus = $gateway->pay($request);
        } catch (\Exception $e) {
            return response()->json([
                'errors'=>  [[
                    'error_message' => $e->getMessage(),
                    'inputs' => []
                ]],
                'redirect' => false
            ]);
        }

        if (isset($paymentStatus[4]) && $paymentStatus[4] === 'cardinity') {
            if ($paymentStatus[3] === 'pending') {
                $auth = session()->get('cardinity.auth');
                $authorization['acsUrl'] = $auth->getAcsUrl();
                $authorization['creq'] = $auth->getCreq();
                return response()->json($authorization, 202);
            } elseif ($paymentStatus[3] === 'approved') {
                $invoice->status = Invoice::PAID;
                $invoice->save();
            } else {
                return response()->json([
                    'errors' => session()->get('cardinity_errors')
                ], 422);
            }
        }


        if (!isset($paymentStatus[0])) {
            if ($checkoutType == 0) {
                //bandaid fix, delete newly created customer account on payment errors to avoid account already exists errors
                //this should cascade and delete all junk
                $customer->delete();
            }

            // $invoice->delete();

            // return Response::json([
            // 	'errors'=>  [['error_message' => 'Payment declined.',
            // 			'inputs' => []
            // 	]],
            // 	'redirect'=>false
            // ]);
        }

        $subTotal = 0;
        foreach ($basket['basketItems'] as $item) {
            if (session()->get('cart.mode') == 'packages') {

                $package = Package::findOrFail($item->id);
                if($package) {
                    if($package->trial != 0) {
                        $trialExpireDate = date('Y-m-d',strtotime(" + ".$package->trial." day"));
                        $trialExpireTime = date('H:i:s');
                        $trialOrder = 1;
                    } else {
                        $trialExpireDate = null;
                        $trialExpireTime = null;
                        $trialOrder = 0;
                    }
                } else {
                    $trialExpireDate = null;
                    $trialExpireTime = null;
                    $trialOrder = 0;
                }

                $order = new Order();
                $order->user_id = Controller::site('id');
                $order->customer_id = $customer->id;
                $order->package_id = $package->id;
                $order->cycle_id = $item->options['cycle_id'];
                $order->last_invoice = date('Y-m-d H:i:s');
                $order->price = $totalPaymentNormal; //$this->getSubTotal($item);
                $order->currency_id = $this->currency->id;
                $order->integration = '';
                $order->trial_order = $trialOrder;
                $order->trial_expire_date = $trialExpireDate;
                $order->trial_expire_time = $trialExpireTime;
                $order->domainIntegration = 0;

                if (session()->has('fraudlabs.response')) {
                    $fraudlabsResponse = session()->get('fraudlabs.response');
                    $order->fraudlabs_status = $fraudlabsResponse['fraudlabspro_status'];
                    $order->fraudlabs_json = json_encode($fraudlabsResponse);
                }

                if (!empty($package->integration)) {
                    $order->integration = $package->integration;
                }

                if (!empty($package->domainIntegration)) {
                    $order->domainIntegration = $package->domainIntegration;
                }

                $order->save();

                $invoice->update([
                    'order_id' => $order->id,
                ]);

                //$this->checkFraud($request, $invoice, $order);

                $this->createOrderOptions($item->options, $order);

                $invoice = Invoice::find($invoice->id);
                if (!empty($order->integration) && $invoice->status == 1) {
                    Integrations::get($order->integration, 'completeOrder', [$order]);
                }

                if (!empty($order->domainIntegration) && $invoice->status == 1) {
                    Integrations::get('domain', 'completeOrder', [$order]);
                }
            }
        }

        //If stripe, create customer on stripe
        if ($request->has('stripe')) {
            if($user->stripeId == "") {
                $userAdmin = User::find(1);
                $stripe = new \Stripe\StripeClient($userAdmin->getSetting('stripe.secretkey'));

                if($request->customer_stripe_id == '') { //check if existing user
                    $customerStripe = $stripe->customers->create([
                        'metadata' => ['user_id' => $user->id],
                        'email' => $user->email,
                    ]);
                }
                else { //check if new user
                    $customerStripe = $stripe->customers->update(
                        $request->customer_stripe_id,
                        [
                            'metadata' => ['user_id' => $user->id],
                            'email' => $user->email,
                        ]
                    );
                }
                $user->stripeId = $customerStripe->id;
                $user->save();
            }
        }

        /**
         * Send mail invoice to user
         */
        $this->sendMailInvoice([
            'user' => $user,
            'customer' => $customer,
            'invoice' => $invoice,
            'currency' => $this->currency,
            'subTotal' => $subTotal,
            'address' => Address::findOrFail($addressId)
        ], $customer, $invoice);

        //pay from invoice hashed
        if(session()->has('cart.mode') && session()->get('cart.mode') == 'invoice')
        {
            return Response::json([
                'valid' => true,
                'redirect' => true
            ]);
        }

        //send order email to admin
        $userFrom = User::find(1);
        //$subject = $user->siteSettings('name') . ' Order #' . $order->id . ' created.';
        //$content = '--Order email notification--';
        //$view = 'Checkout.mail.orderNotificationEmail';
        Mail::to($userFrom)->send(new AdminOrderNotification($order));

        /**
         * Remove session cart and destroy bascket cart
         */
        session()->forget('cart');
        Cart::destroy();

        /** *
         * redirect to receipt page for ajax pinging of
         * status page to get order payment status and
         * product creation status
         */
        return Response::json([
            'valid' => true,
            'redirect' => true,
        ]);
	}

    /**
     * @param Request $request
     * @param $key
     * @return mixed
     */
	public function getFormCheckout(Request $request, $key)
    {
        $cache = Cache::get($key);

        abort_if(is_null($cache), 404);

        if (!empty($cache['customer_id'])) {
            auth()->loginUsingId($cache['customer_id']);
        }

        $package = Package::findOrFail($request->package_id);
        $cycle = $package->cycle($request->cycle_id);

        $cycle_price = $cycle->price;
        $maxDays = date('t');
        $currentDayOfMonth = date('j');

        if ($package->prorate)
				{
					//Monthly
					if($cycle->cycle == 5)
					{
							$cycle_price = ($cycle->price / $maxDays) * ($maxDays - $currentDayOfMonth);
					}

					//every 2 months - 11 months
					if($cycle->cycle >= 6 && $cycle->cycle <= 15)
					{
							$months = 0;
							//2 months
							if($cycle->cycle == 6) $months = 2;
							//3 months
							if($cycle->cycle == 7) $months = 3;
							//4 months
							if($cycle->cycle == 8) $months = 4;
							//5 months
							if($cycle->cycle == 9) $months = 5;
							//6 months
							if($cycle->cycle == 10) $months = 6;
							//7 months
							if($cycle->cycle == 11) $months = 7;
							//8 months
							if($cycle->cycle == 12) $months = 8;
							//9 months
							if($cycle->cycle == 13) $months = 9;
							//10 months
							if($cycle->cycle == 14) $months = 10;
							//11 months
							if($cycle->cycle == 15) $months = 11;

							$dateStart = date_create(date("Y") . "-" . date("m") . "-01");
							$dateCurrent = date_create(date("Y") . "-" . date("m") . "-" . date("d"));
							$daysDiff = date_diff($dateStart, $dateCurrent);

							$dateTotal = date_create(date("Y-m-01", strtotime('+' . $months . ' months')));
							$totalDaysDiff = date_diff($dateStart, $dateTotal);
							$currentDaysDiff = date_diff($dateCurrent, $dateTotal);
							$maxDays = $totalDaysDiff->days;
							$currentDays = $currentDaysDiff->days;

							$cycle_price = ($cycle->price / $maxDays) * $currentDays;
					}

					//every 12 months
					if($cycle->cycle == 16)
					{
							$date1Total = date_create(date("Y") . "-01-01");
							$date2Total = date_create(date("Y") . "-12-31");
							$totalDaysYear = date_diff($date1Total, $date2Total);

							$maxDays = $totalDaysYear->days;
							$currentDayOfYear = date('z');
							$cycle_price = ($cycle->price / $maxDays) * ($maxDays - $currentDayOfYear);
					}

					//every 24 months
					if($cycle->cycle == 17)
					{
							$date1Total = date_create(date("Y") . "-01-01");
							$date2Total = date_create(date("Y-12-31", strtotime('+12 months')));
							$totalDaysYear = date_diff($date1Total, $date2Total);

							$maxDays = $totalDaysYear->days;
							$currentDayOfYear = date('z');
							$cycle_price = ($cycle->price / $maxDays) * ($maxDays - $currentDayOfYear);
					}

					//every 36 months
					if($cycle->cycle == 18)
					{
							$date1Total = date_create(date("Y") . "-01-01");
							$date2Total = date_create(date("Y-12-31", strtotime('+24 months')));
							$totalDaysYear = date_diff($date1Total, $date2Total);

							$maxDays = $totalDaysYear->days;
							$currentDayOfYear = date('z');
							$cycle_price = ($cycle->price / $maxDays) * ($maxDays - $currentDayOfYear);
					}
        }

        $cartItem = Cart::add(
            $package->id,
            $package->name,
            1,
            $cycle_price,
            [
                'fee' => $cycle->fee,
                'cycle_id' => $cycle->id,
                'trial' => $package->trial,
                'options' => []
            ]
        );

        Cart::associate($cartItem->rowId, 'App\Package');

        // Check if this package allows for domain integrations.
        if ($package->domainIntegration) {
            // Process the registration form and get a status.
            $status = Integrations::get('domain', 'processRegistrationForm', [$request]);

            // If the status wasn't true, then there is probably and error which is return with a response.
            // Return the response to the browser so we can show the end user.
            if ($status !== true) {
                return $status;
            }
        }

        if (!empty($package->integration)) {
            Integrations::get($package->integration, 'saveOrderForm', [$request]);
        }

        session()->put('cache_key', $key);
        session()->put('cart.mode', 'packages');

				return $this->getCheckout();
        //return redirect()
          //  ->action('CheckoutController@getCheckout');
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
	public function callback(Request $request)
    {
				$client = Client::create([
						'consumerKey' => $this->user->getSetting('cardinity.consumer_key'),
						'consumerSecret' => $this->user->getSetting('cardinity.consumer_secret')
				]);

        $user = $request->user();
        $invoiceId = session()->get('cardinity.invoiceId');
        $invoice = Invoice::find($invoiceId);
        $transaction = Transactions::where('invoice_id', $invoiceId)->first();
				$auth = session()->get('cardinity.auth');

        $method = new Finalize(
            session()->get('cardinity.paymentId'),
		        $request->cres,
						true
        );

        $client = Client::create([
            'consumerKey' => $invoice->user->getSetting('cardinity.consumer_key'),
            'consumerSecret' => $invoice->user->getSetting('cardinity.consumer_secret')
        ]);

        $errors = [];

        try {
            $payment = $client->call($method);
            $status = $payment->getStatus();
            if ($status == 'approved') {
                $invoice->status = Invoice::PAID;
                $invoice->save();

                session()->forget('cardinity');
								session()->forget('cardinity.paymentId');
								session()->forget('cardinity.invoiceId');
								session()->forget('cardinity.auth');
                $basket = CommonController::factory()->registerBasketInfo();
                $subTotal = 0;
                foreach ($basket['basketItems'] as $item) {
                    if (session()->get('cart.mode') == 'packages') {

                        $package = Package::findOrFail($item->id);
                        if($package) {
                            if($package->trial != 0) {
                                $trialExpireDate = date('Y-m-d',strtotime(" + ".$package->trial." day"));
                                $trialExpireTime = date('H:i:s');
                                $trialOrder = 1;
                            } else {
                                $trialExpireDate = null;
                                $trialExpireTime = null;
                                $trialOrder = 0;
                            }
                        } else {
                            $trialExpireDate = null;
                            $trialExpireTime = null;
                            $trialOrder = 0;
                        }

                        $order = new Order();
                        $order->user_id = Controller::site('id');
                        $order->customer_id = $user->id;
                        $order->package_id = $package->id;
                        $order->cycle_id = $item->options['cycle_id'];
                        $order->last_invoice = date('Y-m-d H:i:s');
                        $order->price = $basket['basketGrendTotalNormal'];
                        $order->currency_id = $this->currency->id;
                        $order->integration = '';
                        $order->trial_order = $trialOrder;
                        $order->trial_expire_date = $trialExpireDate;
                        $order->trial_expire_time = $trialExpireTime;
                        $order->domainIntegration = 0;

                        if (session()->has('fraudlabs.response')) {
                            $fraudlabsResponse = session()->get('fraudlabs.response');
                            $order->fraudlabs_status = $fraudlabsResponse['fraudlabspro_status'];
                            $order->fraudlabs_json = json_encode($fraudlabsResponse);
                        }

                        if (!empty($package->integration)) {
                            $order->integration = $package->integration;
                        }

                        if (!empty($package->domainIntegration)) {
                            $order->domainIntegration = $package->domainIntegration;
                        }

                        $order->save();

                        $invoice->update([
                            'order_id' => $order->id,
                        ]);

                        $this->createOrderOptions($item->options, $order);

                        $invoice = Invoice::find($invoice->id);
                        if (!empty($order->integration) && $invoice->status == 1) {
                            Integrations::get($order->integration, 'completeOrder', [$order]);
                        }

                        if (!empty($order->domainIntegration) && $invoice->status == 1) {
                            Integrations::get('domain', 'completeOrder', [$order]);
                        }
                    }
                }

                $this->sendMailInvoice([
                    'user' => $user,
                    'customer' => $user,
                    'invoice' => $invoice,
                    'currency' => $this->currency,
                    'subTotal' => $subTotal,
                    'address' => Address::findOrFail($invoice->address_id)
                ], $user, $invoice);

                //send order email to admin
                $userFrom = User::find(1);
                $subject = $user->siteSettings('name') . ' Order #' . $order->id . ' created.';
                $view = 'Checkout.mail.orderNotificationEmail';
                Mail::to($userFrom)->send(new OrderNotificationEmail($userFrom, $subject, $order, $view));

                /**
                 * Remove session cart and destroy bascket cart
                 */
                session()->forget('cart');
                Cart::destroy();
            }

            Log::info('cardinity response callback', [$status, $payment->serialize()]);

            $transaction->transaction_id = $payment->getId();
            $transaction->status = $status;
            $transaction->json_response = $payment->serialize();
            $transaction->save();

            return redirect()
                ->action('CheckoutController@getReceipt');

        } catch (InvalidAttributeValue $exception) {
            foreach ($exception->getViolations() as $key => $violation) {
                array_push($errors, $violation->getPropertyPath() . ' ' . $violation->getMessage());
            }
        } catch (ValidationFailed $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, $error['message']);
            }
        } catch (Declined $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, $error['message']);
            }
        } catch (NotFound $exception) {
            foreach ($exception->getErrors() as $key => $error) {
                array_push($errors, $error['message']);
            }
        } catch (Exception $exception) {
            $errors = 'Internal Server Error';
        }

        return redirect('checkout')
            ->with('error_payment', $errors);
    }

    /**
     * @param $md
     * @return bool
     */
    protected function isInvalidCallback($md)
    {
        if ($sMd = session()->get('cardinity.MD')) {
            return $sMd != $md;
        }

        return true;
    }

	public function createUserContact($customer, $contact, $type = 2)
	{
		if (!$customer || !$contact) {
			return null;
		}

		return User_Contact::create([
			'user_id' => $customer->id,
			'address_id' => $contact->id,
			'type' => $type,
		]);
	}

	private function getTaxexempt($request)
	{
		if ($request->has('billingInfo.taxexempt') && !empty($request->input('billingInfo.taxexempt'))) {

			$taxexempt = $request->input('billingInfo.taxexempt');
			Controller::$tax_exempt = $taxexempt;

			return $taxexempt;
		}

		return null;
	}

	public function createUserLink($customer)
	{
		return User_Link::create([
			'user_id' => $customer->id,
			'parent_id' => Controller::site('id'),
		]);
	}

	private function createOrderOptions($options, $order)
	{
		if (isset($options['options']) && sizeof($options['options']) == 0) {
			return null;
		}

		foreach($options['options'] as $option) {
			Order_Options::create([
				'order_id' => $order->id,
				'option_value_id' => $option['id'],
				'amount' => $option['price'],
				'value' => $option['value'],
				'cycle_type' => $option['cycle_type'],
				'status' => Order_Options::SETUP,
				'last_invoice' => date('Y-m-d H:i:s'),
			]);
		}
	}

	private function sendMailInvoice($param, $customer, $invoice)
	{
		//return;

		/**
		 * email receipt
		 */
		$user = $invoice->customer;

		/* Mail::send('Checkout.paidInvoiceEmail', $param, function ($massage) use ($user, $customer, $invoice) {
			$massage->from($user->email, $user->siteSettings('name'));
			$massage->to($customer->email, $customer->name);
			$massage->subject($user->siteSettings('name').' Invoice #'.$user->getSetting('invoice.prefix','').$invoice->invoice_number);
		}); */

		$userFrom = User::find(1);
		$userFromEmail = $userFrom->email;
		$userFromName = $userFrom->siteSettings('name');
		$subject= $userFrom->siteSettings('name').' Invoice #'.$userFrom->getSetting('invoice.prefix','').$invoice->invoice_number;
		$view = 'Checkout.paidInvoiceEmail';
		$content = $param;

		Mail::to($user)->send(new InvoiceEmail($userFromEmail, $userFromName, $subject, $content, $view));
	}

	public function getSubTotal($item)
	{
		if (isset($item->options['fee']) && $item->options['fee'] > 0) {
			return $item->subtotal + ($item->options['fee'] * $item->qty);
		}

		return $item->subtotal;
	}

	public function postDiscountCode(Request $request)
	{
		if(!Settings::get('invoice.discountCode')) { return Response::json(['discount'=>0]); }

		if($request->has('code') && session()->has('cart.subTotal') && session()->has('cart.grandTotal'))
		{
			$codeTest = Discount::where('user_id', Controller::site('id'))
												->where('type', Discount::CODE)
												->where('value', $request->get('code'))
												->where('start','<=', date('Y-m-d h:m:s'))
												->where(function($query){
																$query->where('end','0000-00-00')
																->orWhere('end','>=', date('Y-m-d h:m:s'));
															})
												->first();

			if(!empty($codeTest))
			{
				session()->put('cart.discountCodePercent',$codeTest->discount);
				session()->put('cart.discountCode',$request->get('code'));
				Controller::setCurrency();
				$cart = Controller::formatCartData();

				$grandTotal = session()->get('cart.grandTotal');
				$fixedDiscountPercentage = session()->get('cart.fixedDiscountPercentage');
				$discount = number_format($codeTest->discount / 100 * ($grandTotal * ((100 - $fixedDiscountPercentage) / 100)), 2);
				$discountedTotal = 0;

				if($fixedDiscountPercentage > 0) {
						$discountedTotalFixed = number_format($grandTotal * ((100 - $fixedDiscountPercentage) / 100), 2);
						$discountedTotal = number_format($discountedTotalFixed - $discount, 2);
						$discount = number_format($codeTest->discount / 100 * $discountedTotalFixed, 2);
				}
				else {
						$discountedTotal = number_format($grandTotal - $discount, 2);
				}

				return Response::json([
					'discount'=> $discount,
					'formattedDiscount'=> $this->currency->symbol . $discount,
					'formattedTax'=>$cart['formattedTaxDiscounted'],
					'formattedTotal'=>$cart['formattedGrandTotalDiscounted'],
					'grandTotal'=>$grandTotal,
					'discountedTotal'=>$discountedTotal,
					'formattedDiscountedTotal'=> $this->currency->symbol . $discountedTotal,
				]);

				/* return Response::json([
					'discount'=>$cart['totalDiscounts'],
					'formattedDiscount'=>$cart['formattedTotalDiscounts'],
					'formattedTax'=>$cart['formattedTaxDiscounted'],
					'formattedTotal'=>$cart['formattedGrandTotalDiscounted']
				]); */
			}
		}
		session()->forget('cart.discountCode');
		session()->forget('cart.discountCodePercent');
		return Response::json(['discount'=>0]);
	}

	public function postTaxRates(Request $request)
	{
		$county = $request->input('county');

		session()->put('cart.county_id', $county);

		$arrRates = DB::table('taxRates')->join('taxZones','taxRates.zone_id','=','taxZones.id')
			->join('taxZoneCounties','taxZoneCounties.zone_id','=','taxZones.id')
			->where('taxZoneCounties.county_id','=', $county)
			->get();

		$arrRates2 = [];
		$taxRate = 0;
		foreach($arrRates as $key => $rate) {
			$arrRates2[$rate->class_id] = $rate->rate;
			$taxRate = $rate->rate;
		}

		session()->put('cart.taxrates', $taxRate);

		/**
		 * Get bascket cart info
		 */
		$data = CommonController::factory()->registerBasketInfo();

		$default_currency = Controller::setDefaultCurrency();
		$currency = $this->currency;
		return json_encode([
			'taxName' => $taxRate,
			'tax' => ($data['basketTotal'] * $taxRate / 100)  / $default_currency->conversion * $currency->conversion,
			'grandTotal' => $data['basketGrendTotal'] / $default_currency->conversion * $currency->conversion,
			'currency' => $currency ? $currency->symbol : null,
		], 1);
	}

	private function adjustForChosenCurrency($amount)
	{

		return $amount;
	}

	public function postCheckoutOrderStatus(Request $request)
	{
		if ($request->ajax() && $request->has('orderId')) {
			//ajax periodically checks this url to determine if orders have been processed and setup
		}
	}

	public function postCheckoutGetRegions(Request $request)
	{
		if ($request->ajax() && $request->has('country'))
		{
			$this->validate($request, [
				'country' => 'required|numeric'
			]);

			$counties = $this->getRegions($request->input('country'));

			return Response::json($counties);
		}
	}

	private function getRegions($country)
	{
		return Counties::select('name','id')->where('country_id','=', $country)->get();
	}

	public function postCheckoutCheckUsername(Request $request)
	{
		if ($request->ajax() && $request->has('username'))
		{
			$this->validate($request, [
				'username' => 'required'
			]);
			if($this->checkUsernameAvailable($request->input('username')))
			{
				return Response::json(['exists'=>false]);
			}
			else
			{
				return Response::json(['exists'=>true]);
			}

		}
	}

	private function checkUsernameAvailable($username)
	{
		if(empty(DB::table('users')->select('users.id')->join('user_link', 'user_link.user_id', '=', 'users.id')->where('users.username','=',$username)->where('user_link.parent_id','=',Controller::site('id'))->get()))
		{
			return true;
		}
		return false;
	}

	private function checkEmailAvailable($email)
	{
		if(empty(DB::table('users')->select('users.id')->join('user_link', 'user_link.user_id', '=', 'users.id')->where('users.email','=',$email)->where('user_link.parent_id','=',Controller::site('id'))->get()))
		{
			return true;
		}
		return false;
	}

	public function postCheckoutCheckEmail(Request $request)
	{
		if ($request->ajax() && $request->has('email'))
		{
			$this->validate($request, [
				'email' => 'required'
			]);
			if($this->checkEmailAvailable($request->input('email')))
			{
				return Response::json(['exists'=>false]);
			}
			else
			{
				return Response::json(['exists'=>true]);
			}

		}
	}

	public function getReceipt(Request $request)
	{
		$user = User::findOrFail(Controller::site('id'));

		$transaction = null;
		if(session()->has('receipt.transaction_id') && !empty(session()->get('receipt.transaction_id'))) {
			$transactionId = session()->get('receipt.transaction_id');
			$transaction = Transactions::where('id',$transactionId)->first();
		} elseif($request->has('transId') && $request->has('transKey')) {
			$transactionId = $request->get('transId');
			$transactionKey = $request->get('transKey');
			$transaction = Transactions::where('id',$transactionId)->where('transaction_key',$transactionKey)->first();
		}

		if(empty($transaction)) {
			abort(403, 'Transaction Not Found or Unauthorized');
		}

		$totalOptionCost = 0;
		$subTotal = 0;
		foreach($transaction->invoice->items as $k=>$v) {
			$totalItemCost = 0;
			if(isset($v->invoice->order->package) && $v->invoice->order->package->trial == 0)
			{
				$subTotal += $v->price * $v->quantity;
			}
		}
		$cart = [
			'grandTotal' => 0,
			'formattedGrandTotal' => $transaction->currency->symbol."0.00",
			'availableCurrencies' => Currency::whereIn('id',Settings::get('invoice.currency',[3,4,5]))->get()
		];

		$files = [];
		$packages = [];
		foreach($transaction->invoice->items as $item) {
			if(!empty($item->package_id)) {
				$package = Package::find($item->package_id);
				$packages[] = $package;
				if(!empty($package->files) && $transaction->invoice->status == 1) {
					foreach($package->files as $file) {
						$files[] = $file;
					}
				}
			}
		}

		//Calculate Options
		$options = $this->getOrderOptions($transaction->invoice);

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
		$subTotal += $totalOptionCost;

		$receiptData = [
            'default_currency' => Controller::setDefaultCurrency(),
            'invoice'   => $transaction->invoice,
            'transaction'   => $transaction,
            'user' => $transaction->invoice->user,
            'customer'  => $transaction->invoice->customer,
            'currency'  => $transaction->currency,
            'subTotal'  => $subTotal,
            'cart'      => $cart,
            'packages'   => $packages,
            'files' => $files,
            'options' => $options
        ];

		$cacheKey = session()->get('cache_key');
		if ($cacheKey) {
		    $cacheData = Cache::pull($cacheKey);
		    return redirect()->away($cacheData['callback_url'])->with($receiptData);
        } else {
						if($request->session()->has('cpanel.out_of_qty'))
						{
								$invoice = $transaction->invoice;
								$invoice->status = Invoice::UNPAID;
                $invoice->save();
						}
            return view('Checkout.receipt', $receiptData);
        }
	}

    /**
     * @param array $data
     * @param array $params
     */
//	private function redirectCallbackWhenPaymentSuccess(array $data, array $params)
//    {
//        $ch = curl_init();
//        $options = [
//            CURLOPT_URL => $data['callback_url'],
//            CURLOPT_POST => true,
//            CURLOPT_POSTFIELDS => http_build_query($params),
//            CURLOPT_FOLLOWLOCATION => true,
//        ];
//
//        curl_setopt_array($ch, $options);
//        curl_exec($ch);
//
//        if (curl_errno($ch)) {
//            abort(404, curl_error($ch));
//        }
//
//        curl_close($ch);
//    }

	private function validateCheckout_HashedUser_AccountInfo($request)
	{
		$errorResponse = [];

		if($request->has('account'))
		{
			$account = $request->input('account');
			if(isset($account['username']) && !empty($account['username']))
			{
				if($account['password'] !== $account['password2'])
				{
					$errorResponse[] = [
						'error_message' => 'Passwords do not match.',
						'inputs' => [
							'account[password]',
							'account[password2]'
						]
					];
				}
				if(!isset($account['password']) || (isset($account['password']) && empty($account['password'])))
				{
					$errorResponse[] = [
						'error_message' => 'A password is required.',
						'inputs' => [
							'account[password]'
						]
					];
				}
				if(!isset($account['password2']) || (isset($account['password2']) && empty($account['password2'])))
				{
					$errorResponse[] = [
						'error_message' => 'You must enter your password a second time.',
						'inputs' => [
							'account[password2]'
						]
					];
				}
				if(isset($account['password']) && isset($account['password2']) && $account['password'] !== $account['password2'])
				{
					$errorResponse[] = [
						'error_message' => 'Your passwords must match.',
						'inputs' => [
							'account[password]',
							'account[password2]'
						]
					];
				}
				if(!$this->checkUsernameAvailable($account['username']))
				{
					$errorResponse[] = [
						'error_message' => 'Username is not available.',
						'inputs' => [
							'account[username]'
						]
					];
				}
			}
		}

		return $errorResponse;
	}

	private function validateCheckout_LoggedInUser($request)
	{
		$errorResponse = [];

		return $errorResponse;
	}

	private function validateCheckout_PaymentMethod($request)
	{
		$errorResponse = [];

		if(!$request->has('term'))
		{
			$errorResponse[] = [
				'error_message' => 'Agreements is required.',
				'inputs' => [
					'term'
				]
			];
		}

		/*

		if(!$request->has('paymentMethod'))
		{
			$errorResponse[] = [
				'error_message' => 'Payment method is required.',
				'inputs' => [
					'paymentMethod'
				]
			];
		}

		if(!$request->has('paymentMethod.type'))
		{
			$errorResponse[] = [
				'error_message' => 'Payment method is required.',
				'inputs' => [
					'paymentMethod[type]'
				]
			];
		}

		*/

		return $errorResponse;
	}

	private function findUserByUserName($username)
	{
		return User::where('username', $username)->first();
	}

	private function validateCheckout_NewUser($request)
	{
		$accType = null;
		$loggedIn = Auth::check();
		if ($loggedIn && $user = Auth::User()) {
			$accType = $user->account_type;
		}

		if (!empty($request->input('account.username')) && Auth::User()) {
			if ($this->findUserByUserName($request->input('account.username'))) {
				$errorResponse[] = [
					'error_message' => 'this user already exists.',
					'inputs' => [
						'account[username]',
					]
				];
			}
		}

		if (!$request->has('account') && $accType === null) {
			$errorResponse[] = [
				'error_message' => 'You must be logged in or create a account to checkout.',
				'inputs' => [
					'account[email]',
					'account[username]',
					'account[password]',
					'account[password2]',
				]
			];
		}

		if ($request->has('account') && $accType !== 2 && $accType !== null) {
			$errorResponse[] = [
				'error_message' => 'Your are logged in with your BillingServ account. You must be logged in with a customer account to checkout.',
				'inputs' => []
			];
		}

		if ($request->has('account') && $accType === null) {
			$account = $request->input('account');

			if ($account['password'] !== $account['password2']) {
				$errorResponse[] = [
					'error_message' => 'Passwords do not match.',
					'inputs' => [
						'account[password]',
						'account[password2]'
					]
				];
			}

			if (!isset($account['username']) || (isset($account['username']) && empty($account['username']))) {
				$errorResponse[] = [
					'error_message' => 'Email is required.',
					'inputs' => [
						'account[username]'
					]
				];
			}

			if (!$this->checkUsernameAvailable($account['username'])) {
				$errorResponse[] = [
					'error_message' => 'Email already exists.',
					'inputs' => [
						'account[username]'
					]
				];
			}

			if (!isset($account['email']) || (isset($account['email']) && empty($account['email']))) {
				$errorResponse[] = [
					'error_message' => 'Email address is required.',
					'inputs' => [
						'account[email]'
					]
				];
			}


			if (!isset($account['password']) || (isset($account['password']) && empty($account['password']))) {
				$errorResponse[] = [
					'error_message' => 'A password is required.',
					'inputs' => [
						'account[password]'
					]
				];
			}

			if (!isset($account['password2']) || (isset($account['password2']) && empty($account['password2']))) {
				$errorResponse[] = [
					'error_message' => 'You must enter your password a second time.',
					'inputs' => [
						'account[password2]'
					]
				];
			}

			if (isset($account['password']) && isset($account['password2']) && $account['password'] !== $account['password2']) {
				$errorResponse[] = [
					'error_message' => 'Your passwords must match.',
					'inputs' => [
						'account[password]',
						'account[password2]'
					]
				];
			}
		}

		return $errorResponse;
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

	public function paymentItents()
	{
			try {
					$userAdmin = User::find(1);
					Stripe::setApiKey($userAdmin->getSetting('stripe.secretkey'));
					$intent = PaymentIntent::create([
							'amount' => session()->get('cart.grandTotal') * 100,
							'currency' => Controller::setCurrency()->short_name,
					]);
					ob_start();
					echo("New Order: " . $intent->id);
					error_log(ob_get_clean(), 4);
					return response()->json($intent);
			} catch (Exception $e) {
					return back()->withErrors('Error! ' . $e->getMessage());
			}
	}

	public function getOrAddCustomerStripe()
	{
		$userAdmin = User::find(1);
		$stripe = new \Stripe\StripeClient($userAdmin->getSetting('stripe.secretkey'));

		$user = Auth::check();

		if($user) //existing user
		{
			$user = Auth::user();
			try {
				$result = $stripe->customers->retrieve(
								  $user->stripeId,
								  []
								);
			}
			catch (\Exception $e) {
				$result = $stripe->customers->create([
					'metadata' => ['user_id' => $user->id],
				  'email' => $user->email,
				]);
				$user->stripeId = $result->id;
				$user->save();
			}
			return $result;
		}
		else //unlogged user
		{
				$result = $stripe->customers->create([
				]);
				Stripe::setApiKey($userAdmin->getSetting('stripe.secretkey'));
				$intent = \Stripe\SetupIntent::create([
										'customer' => $result->id
									]);
				$resultAll['id'] = $result->id;
				$resultAll['client_secret'] = $intent->client_secret;
				return $resultAll;
		}
	}

	public function createSetupIntentStripe(Request $request)
	{
			$userAdmin = User::find(1);
			Stripe::setApiKey($userAdmin->getSetting('stripe.secretkey'));
			$intent = \Stripe\SetupIntent::create([
									'customer' => $request->stripeId
								]);
			return $intent;
	}

	public function getPaymentIntentSavedCardStripe(Request $request)
	{
		$userAdmin = User::find(1);
		Stripe::setApiKey($userAdmin->getSetting('stripe.secretkey'));

		try {
			$paymentIntent = PaymentIntent::create([
				'amount' => $request->amount * 100,
				'currency' => $request->currency,
				'customer' => $request->customer,
				'payment_method' => $request->payment_method,
				'off_session' => true,
				'confirm' => true,
			]);
			return $paymentIntent;
		}
		catch (CardException $e) {
			// Error code will be authentication_required if authentication is needed
			$paymentIntent['error_code'] = $e->getError()->code;
			$paymentIntent['payment_intent_id'] = $e->getError()->payment_intent->id;
			$paymentIntent['payment_intent'] = PaymentIntent::retrieve($paymentIntent['payment_intent_id']);
			return $paymentIntent;
		}
	}

	public function testpaypalpro()
	{
		$payflow = new Payflow;
		$payflow->setEnv('sandbox');
		$payflow->setPartner('PayPal');
		$payflow->setVendor('BaseServ');
		$payflow->setCurrency('GBP');
		$payflow->setUser('BaseServ');
		$payflow->setPassword('QiG%Yu2tZ6$1ypoFyfUf8NXNOtRShlGC');
		$payflow->data['ACCT'] = '4111111111111111';
		$payflow->data['AMT'] = '13';
		$payflow->data['CVV2'] = '123';
		$payflow->data['EXPDATE'] = '1125';
		$payflow->data['EMAIL'] = 'coba@mail.com';
		$payflow->data['FIRSTNAME'] = 'coba';
		$payflow->data['BILLTOFIRSTNAME'] = 'coba';
		$payflow->data['SHIPTOFIRSTNAME'] = 'coba';
		$payflow->data['LASTNAME'] = 'aja';
		$payflow->data['BILLTOLASTNAME'] = 'aja';
		$payflow->data['SHIPTOLASTNAME'] = 'aja';
		$payflow->data['STREET'] = 'Street 123';
		$payflow->data['BILLTOSTREET'] = 'Street 123';
		$payflow->data['SHIPTOSTREET'] = 'Street 123';
		$payflow->data['CITY'] = 'London';
		$payflow->data['BILLTOCITY'] = 'London';
		$payflow->data['SHIPTOCITY'] = 'London';
		$payflow->data['STATE'] = 'VIC';
		$payflow->data['BILLTOSTATE'] = 'VIC';
		$payflow->data['SHIPTOSTATE'] = 'VIC';
		$payflow->data['ZIP'] = '3000';
		$payflow->data['BILLTOZIP'] = '3000';
		$payflow->data['SHIPTOZIP'] = '3000';
		$payflow->data['COUNTRY'] = 'UK';
		$payflow->data['BILLTOCOUNTRY'] = 'UK';
		$payflow->data['SHIPTOCOUNTRY'] = 'UK';
		$result = $payflow->pay();
		if ($result['success']) {
			$token = $result['data']['PNREF'];
		}

	}

	public function validateVat(Request $request)
	{
			$vatCountryIso = substr($request->vatNumber,0, 2);
			$vatNumber = substr($request->vatNumber,2, strlen($request->vatNumber)-2);
			$oVatValidator = new VatValidator(new Europa, $vatNumber, $vatCountryIso);

			if ($oVatValidator->check())
			{
					//do remove VAT if user's country with VAT country is different
					$country_id = $request->country_id;
					$vatCountryIso = substr($request->vatNumber,0, 2);
					$country = Countries::where('iso2', $vatCountryIso)->first();

					//get admin country
					$admin = User::find(1);
					$admin_country_id = $admin->mailingContact->address->country_id;

					$useVat = true;
					if($country_id != $country->id)
					{
							return response()->json([
								'success' => false,
								'errors' => ["vatNumber" => [trans('frontend.chk-vatnumberinvalid')]]
							], 200 );
					}

					//if admin country same with customer country with same VAT country do NOT removed VAT
					if($admin_country_id == $country_id && $country_id == $country->id)
					{
							$useVat = false;
							return response()->json([
									'success' => true,
									'use_vat' => $useVat,
									'message' => trans('frontend.chk-vatnumbercorrect')
								],
								200
							);
					}

					//if admin country NOT same country with customer country with same VAT country do remove VAT
					if($admin_country_id != $country_id && $country_id == $country->id)
					{
							$useVat = true;
							return response()->json([
									'success' => true,
									'use_vat' => $useVat,
									'message' => trans('frontend.chk-vatnumbercorrect')
								],
								200
							);
					}

					return response()->json([
							'success' => true,
							'use_vat' => $useVat,
							'message' => trans('frontend.chk-vatnumbercorrect')
						],
						200
					);
			}
			else
			{
					return response()->json([
						'success' => false,
						'errors' => ["vatNumber" => [trans('frontend.chk-vatnumberinvalid')]]
					], 200 );
			}
	}

	public function checkVat($vat, Request $request)
	{
			$vatCountryIso = substr($vat,0, 2);
			$vatNumber = substr($vat,2, strlen($vat)-2);
			$oVatValidator = new VatValidator(new Europa, $vatNumber, $vatCountryIso);

			if ($oVatValidator->check())
			{
					$sFormattedRequestDate = (new \DateTime)->format('d-m-Y');

					echo 'Business Name: ' . $oVatValidator->getName() . '<br />';
			    echo 'Address: ' . $oVatValidator->getAddress() . '<br />';
			    echo 'Request Date: ' . $sFormattedRequestDate . '<br />';
			    echo 'Member State: ' . $oVatValidator->getCountryCode() . '<br />';
			    echo 'VAT Number: ' . $oVatValidator->getVatNumber() . '<br />';
			}
			else
			{
					echo 'Invalid VAT number';
			}
	}

	public function checkFraud(Request $request, $invoice = null, $order = false)
	{
        return response()->json([
            'success' => true,
            'action' => 'execute_payment'
        ], 200);
	}

	protected function fraudLabsCheck(Request $request)
    {
        // Configures FraudLabs Pro API key
        $userAdmin = User::find(1);
        $skipCheckExistingOrder = false;
        if ($userAdmin->getSetting('fraudlabs.skipCheckExisting')) $skipCheckExistingOrder = true;

        if ($userAdmin->getSetting('integration.fraudlabs') && !$skipCheckExistingOrder) {
            FraudConfiguration::apiKey($userAdmin->getSetting('fraudlabs.apiKey'));
            $county = Counties::find($request->input('billingAddress.region'));
            $country = Countries::find($request->input('billingAddress.country'));

            $basket = CommonController::factory()->registerBasketInfo();
            $totalQuantity = 0;
            foreach ($basket['basketItems'] as $item) {
                $totalQuantity += $item->qty;
            }

            $orderDetails = [
                'order' => [
                    //'orderId'		=> '',
                    'note'			=> 'Bserv',
                    'currency'		=> $this->currency->short_name,
                    'amount'		=> $basket['basketGrendTotal'],
                    'quantity'		=> $totalQuantity,
                    'paymentMethod'	=> FraudOrder::CREDIT_CARD,
                ],
                'card' => [
                    'number'	=> $request->input('paymentMethod.number'),
                ],
                'billing'	=> [
                    'firstName'	=> $request->input('billingInfo.firstname'),
                    'lastName'	=> $request->input('billingInfo.lastname'),
                    'email'		=> $request->input('billingInfo.email'),
                    'phone'		=> $request->input('billingInfo.phone'),
                    'address'	=> $request->input('billingAddress.address1'),
                    'city'		=> $request->input('billingAddress.city'),
                    'state'		=> $county ? $county->code : '',
                    'postcode'	=> $request->input('zip'),
                    'country'	=> $country ? $country->iso2 : '',
                ],
            ];

            // Sends the order details to FraudLabs Pro

            $result = FraudOrder::validate($orderDetails);
            $result = (array) $result;

            //sample response (for testing purpose only)
            /*
            $result['is_country_match'] = 'NA';
            $result['is_high_risk_country'] = 'Y';
            $result['distance_in_km'] = '-';
            $result['distance_in_mile'] = '-';
            $result['ip_country'] = '-';
            $result['ip_continent'] = null;
            $result['ip_region'] = '-';
            $result['ip_city'] = '-';
            $result['ip_latitude'] = '0';
            $result['ip_longitude'] = '0';
            $result['ip_timezone'] = '-';
            $result['ip_elevation'] = '0';
            $result['ip_domain'] = '-';
            $result['ip_mobile_mnc'] = 'NA';
            $result['ip_mobile_mcc'] = 'NA';
            $result['ip_mobile_brand'] = 'NA';
            $result['ip_netspeed'] = '-';
            $result['ip_isp_name'] = 'Private IP Address LAN';
            $result['ip_usage_type'] = '';
            $result['is_free_email'] = 'NA';
            $result['is_new_domain_name'] = 'NA';
            $result['is_domain_exists'] = 'NA';
            $result['is_proxy_ip_address'] = 'N';
            $result['is_bin_found'] = 'NA';
            $result['is_bin_country_match'] = 'NA';
            $result['is_bin_name_match'] = 'NA';
            $result['is_bin_phone_match'] = 'NA';
            $result['is_bin_prepaid'] = 'NA';
            $result['is_address_ship_forward'] = 'NA';
            $result['is_bill_ship_city_match'] = 'NA';
            $result['is_bill_ship_state_match'] = 'NA';
            $result['is_bill_ship_country_match'] = 'NA';
            $result['is_bill_ship_postal_match'] = 'NA';
            $result['is_ship_address_blacklist'] = 'NA';
            $result['is_phone_blacklist'] = 'NA';
            $result['is_ip_blacklist'] = 'NA';
            $result['is_email_blacklist'] = 'NA';
            $result['is_credit_card_blacklist'] = 'NA';
            $result['is_device_blacklist'] = 'NA';
            $result['is_user_blacklist'] = 'NA';
            $result['is_high_risk_username'] = 'NA';
            $result['is_export_controlled_country'] = 'NA';
            $result['is_malware_exploit'] = 'NA';
            $result['user_order_id'] = '';
            $result['user_order_memo'] = '';
            $result['fraudlabspro_score'] = '2';
            $result['fraudlabspro_distribution'] = '22';
            $result['fraudlabspro_status'] = 'APPROVE'; //APPROVE / REJECT / REVIEW
            $result['fraudlabspro_id'] = '20200728-GOCME6';
            $result['fraudlabspro_version'] = '1.5.1';
            $result['fraudlabspro_error_code'] = '';
            $result['fraudlabspro_message'] = '';
            $result['fraudlabspro_credits'] = 490;
            */

            session()->put('fraudlabs.response', $result);

            if ($user = $request->user()) {
                $user->update([
                    'fraudlabs_status' => $result['fraudlabspro_status'],
                    'fraudlabs_json' => json_encode($result)
                ]);
            }

            $fraudlabsStatus = $result['fraudlabspro_status'];

            if ($userAdmin->getSetting('fraudlabs.riskScore')) {
                if($result['fraudlabspro_score'] > $userAdmin->getSetting('fraudlabs.riskScore')) $fraudlabsStatus = "REJECT";
            }

            if ($userAdmin->getSetting('fraudlabs.rejectFreeEmail')) {
                if($result['is_free_email'] == 'Y') $fraudlabsStatus = "REJECT";
            }

            if ($userAdmin->getSetting('fraudlabs.rejectCountryMismatch')) {
                if($result['is_country_match'] == 'N') $fraudlabsStatus = "REJECT";
            }

            if ($userAdmin->getSetting('fraudlabs.rejectAnonymousNetworks')) {
                if($result['is_proxy_ip_address'] == 'Y') $fraudlabsStatus = "REJECT";
            }

            if ($userAdmin->getSetting('fraudlabs.rejectHighRiskCountry')) {
                if($result['is_high_risk_country'] == 'Y') $fraudlabsStatus = "REJECT";
            }

            session()->put('fraudlabs.status', $fraudlabsStatus);

            if (in_array($fraudlabsStatus, ['REJECT', 'REJECT_BLACKLIST'])) throw new \Exception('get-fraud');
            else {
                FraudOrder::feedback([
                    'id'		=> $result['fraudlabspro_id'],
                    'status'	=> FraudOrder::APPROVE,
                    'note'		=> 'This customer made a valid purchase before.',
                ]);
            }
        }
    }

		public function checkOutofstock(Request $request)
		{
				$package_id = $request->package_id;
				if($package_id == '')
				{
						return response()->json([
								'success' => true,
								'action' => 'execute_payment'
						], 200);
				}

				$packageSetting = PackageSetting::where('package_id', $package_id)
																					->where('name', 'like', '%server')
																					->first();

				if(!$packageSetting)
				{
						return response()->json([
								'success' => true,
								'action' => 'execute_payment'
						], 200);
				}

				$out_of_stock = false;

				//cpanel
				if($packageSetting->name == 'cpanel.server')
				{
						$integrationCpanel = IntegrationCpanel::find($packageSetting->value);
						$totalAccount = Order_Settings::where('setting_name', 'cpanel.server')
																						->where('setting_value', $integrationCpanel->id)
																						->count();

						if($totalAccount >= $integrationCpanel->qty)
						{
								$out_of_stock = true;
						}
				}

				//directadmin
				if($packageSetting->name == 'directadmin.server')
				{
						$integration = Integration::find($packageSetting->value);
						$totalAccount = Order_Settings::where('setting_name', 'directadmin.server')
																						->where('setting_value', $integration->id)
																						->count();

						if($totalAccount >= $integration->qty)
						{
								$out_of_stock = true;
						}
				}

				if($out_of_stock)
				{
						return response()->json([
								'success' => true,
								'action' => 'out_of_stock',
								'message' => trans('frontend.chk-outofqty')
						], 200);
				}

				return response()->json([
						'success' => true,
						'action' => 'execute_payment'
				], 200);

		}
}
