<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Permissions;
use Route;
use Settings;
use App\User_Setting;
use Session;

use App\MiscStorage;
use App\Packages\APIs\PayPal;
use arleslie\TwoCheckout\Base as TwoCheckout;
use epjwhiz2\Bluepay\Bluepay;
use Illuminate\Http\Request;

// For Stripe Publishable key validation.
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use Cardinity\Client as CardinityClient;
use Cardinity\Method\Payment as CardinityPayment;
use Cardinity\Exception as CardinityException;

class PaymentGatewayController extends Controller
{
	public function __construct()
	{
		if (!Permissions::has('settings')) {
			throw new Permissions::$exception;
		}
	}

	protected $gateways = [
		'paypalpro'      => 'PayPalPro',
		'authorize'      => 'Authorize',
		'bluepay'        => 'BluePay',
		'stripe'         => 'Stripe',
		'paypalstandard' => 'PayPalStandard',
		'worldpay'       => 'WorldPay',
		'cardinity'      => 'Cardinity',
		'banktransfer'   => 'BankTransfer',
		'2checkout'      => '2Checkout',
		'merchantfocus'  => 'MerchantFocus',
		'gocardless'     => 'GoCardless',
		'pymtpro' => 'PYMTPro'
	];

	protected $onsiteGateways = [
		'paypalpro',
		'authorize',
		'bluepay',
		'stripe',
		'worldpay',
		'cardinity',
		'banktransfer',
		'gocardless'
	];

	protected $offsiteGateways = [
		'paypalstandard',
		'worldpay',
		'2checkout',
		'pymtpro'
	];

	public function get($gateway = '')
	{
		Route::current()->setUri('settings/paymentgateways');

		if (!empty($this->gateways[$gateway])) {
			return $this->{'get'.$this->gateways[$gateway]}();
		}

		return $this->getGateways();
	}

	public function clear($gateway = '')
	{
		$default = Settings::get('site.defaultGateway');
		if($default === $gateway)
		{
			User_Setting::where('name','site.defaultGateway')->where('user_id',Auth::User()->id)->delete();
		}
		User_Setting::where('name','LIKE',$gateway.'.%')->where('user_id',Auth::User()->id)->delete();
		return redirect('/settings/paymentgateways');
	}

	public function post(Request $request, $gateway = '')
	{
		if (!empty($this->gateways[$gateway])) {
			$result = $this->{'post'.$this->gateways[$gateway]}($request);

			if (is_array($result)) {
				if (isset($result[0])) {
					if (in_array($gateway, $this->onsiteGateways)) {
						Settings::set([
							'site.defaultGateway' => $gateway
						]);
					} else {
						Settings::set([
							"gateway.enabled.{$gateway}" => true
						]);
					}

					return redirect('/settings/paymentgateways/'.$gateway)->with('status', $result[1]);
				} else {
					return back()->withInput()->withErrors($result[1]);
				}
			} else {
				return $result;
			}
		}

		return abort(404);
	}

	public function getGateways()
	{
        $user = Auth::User();
        $gateways = DB::table('user_settings')
            ->select('name','value')
            ->where('user_id', $user->id)
            ->where('name','LIKE','gateway.enabled.%')->get();
        $data['gateways'] = [];
        foreach($gateways as $k=>$v)
        {
            $gateway = explode('gateway.enabled.',$v->name);
            $data['gateways'][$gateway[1]] = $v->value;
        }
        $data['default'] = Settings::get('site.defaultGateway');

		return view('Settings.paymentgatewaysListing', $data);
	}

	public function postClearGateway(Request $request)
	{
		$user = Auth::User();
		$gateway = $request->input('gateway');
		if (!empty($this->gateways[$gateway])) {
			DB::table('user_settings')
				->where('user_id', $user->id)
				->where('name','LIKE',$gateway.'.%')->delete();
			DB::table('user_settings')
				->where('user_id', $user->id)
				->where('name','LIKE','gateway.enabled.'.$gateway)->delete();
			DB::table('user_settings')->insert([
				'name' => 'gateway.enabled.'.$gateway,
				'value' => 0,
				'user_id' => $user->id
			]);
		}
	}

	public function getPayPalPro()
	{
		return view('Settings.paymentgateways.paypalproForm');
	}

	public function postPayPalPro(Request $request)
	{
		$this->validate($request, [
			'vendor' => 'required',
			'user' => 'required',
			'password' => 'required'
		]);

		Settings::set([
			'paypalpro.vendor' => $request->input('vendor'),
			'paypalpro.user' => $request->input('user'),
			'paypalpro.password' => $request->input('password'),
			'paypalpro.testmode' => $request->has('testmode')
		]);

		return [1,'Authentication details tested and saved successfully.'];
	}
	public function getAuthorize()
	{
		return view('Settings.paymentgateways.authorizeForm');
	}

	public function getBluePay()
	{
		return view('Settings.paymentgateways.bluepayForm');
	}

	public function postBluePay(Request $request)
	{
		$this->validate($request, [
			'account_id' => 'required|numeric',
			'secretkey' => 'required'
		]);

		try {
			$bluepay = new Bluepay(
				$request->input('account_id'),
				$request->input('secretkey'),
				$request->has('testmode')
			);

			$check = $bluepay->process('AUTH');

			if ($check['MESSAGE'] === "SECURITY ERROR") {
				return [0,"Invalid Account ID or Secret Key."];
			}
		} catch (\Exception $e) {
			return [0,'Unable to verify the information provided.'];
		}

		Settings::set([
			'bluepay.account_id' => $request->input('account_id'),
			'bluepay.secretkey' => $request->input('secretkey'),
			'bluepay.testmode' => $request->has('testmode')
		]);

		return [1,'Authentication details tested and saved successfully.'];
	}

	public function getStripe()
	{
		return view('Settings.paymentgateways.stripeForm');
	}

	public function postStripe(Request $request)
	{
		$this->validate($request, [
			'secretkey' => 'required',
			'publishablekey' => 'required'
		]);

		\Stripe\Stripe::setApiKey($request->input('secretkey'));

		try {
			\Stripe\SKU::all();
		} catch (\Exception $e) {
			return [0,"Invalid Secret Key Provided."];
		}

		try {
			$guzzle = new Guzzle();
			$guzzle->get("https://api.stripe.com/v1/tokens?key=" . $request->input('publishablekey'));
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			if ($e->getCode() === 401) {
				return [0,"Invalid Publishable Key Provided."];
			}
		}

		Settings::set([
			'stripe.secretkey' => $request->input('secretkey'),
			'stripe.publishablekey' => $request->input('publishablekey')
		]);

		return [1,'API keys were validated and have been saved.'];
	}

	public function getPayPalStandard()
	{
		return view('Settings.paymentgateways.paypalstandardForm');
	}

	public function postPayPalStandard(Request $request)
	{
		$this->validate($request, [
			'email' => 'required|email'
		]);

		$test = 0;
		if($request->has('testmode'))
		{
			$test = 1;
		}
		Settings::set(['paypalstandard.email' => $request->input('email'),'paypalstandard.testmode' => $test]);

		return [1,'Email address has been saved.'];
	}

	public function getWorldPay()
	{
		return view('Settings.paymentgateways.worldpayForm');
	}

	public function postWorldPay(Request $request)
	{
		$testmode = 0;
		if($request->has('testmode'))
		{
			$testmode = 1;
		}

		if(!$request->has('testServiceKey') && !$request->has('liveServiceKey') && !$request->has('testClientKey') && !$request->has('liveClientKey'))
		{
			//return [0,"Must at least provide a Test Service Key and a Test Client Key."];
		}

		if($request->has('testServiceKey') && $request->testServiceKey != '')
		{
			try {
				$guzzle = new Guzzle();
				$response = $guzzle->request('GET', 'https://api.worldpay.com/v1/orders?environment=TEST&fromDate='.date('Y-m-d',time()-86400).'&toDate='.date('Y-m-d'), [
					'headers' => [
						'Authorization' => $request->input('testServiceKey'),
						'Content-type'     => 'application/json'
					]
				]);
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				return [0,"Invalid Test Service Key."];
			}
		}


		if($request->has('liveServiceKey') && $request->liveServiceKey != '')
		{
			try {
				$response = $guzzle->request('GET', 'https://api.worldpay.com/v1/orders?environment=LIVE&fromDate='.date('Y-m-d',time()-86400).'&toDate='.date('Y-m-d'), [
					'headers' => [
						'Authorization' => $request->input('liveServiceKey'),
						'Content-type'     => 'application/json'
					]
				]);
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				return [0,"Invalid Live Service Key."];
			}
		}

		/*if($request->has('testClientKey'))
		{
			try {
				$response = $guzzle->request('POST', 'https://api.worldpay.com/v1/tokens', [
					'json' => [
						"reusable"=>false,
						"paymentMethod"=>[
							"type"=>"Card",
							"name"=>"fake guy",
							"expiryMonth"=>"12",
							"expiryYear"=>date('Y',time()+31536000),
							"cardNumber"=>$request->input('testClientKey'),
							"cvc"=>"123"
						],
						"clientKey"=>"1"
					]
				]);
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				return [0,"Invalid Test Client Key."];
			}
		}*/

		Settings::set([
			'worldpay.liveClientKey' => $request->input('liveClientKey'),
			'worldpay.liveServiceKey' => $request->input('liveServiceKey'),
			'worldpay.testClientKey' => $request->input('testClientKey'),
			'worldpay.testServiceKey' => $request->input('testServiceKey'),
			'worldpay.testmode' => $testmode
		]);
		return [1,'API keys were validated and have been saved.'];
	}

	public function getCardinity()
	{
		return view('Settings.paymentgateways.cardinityForm');
	}

	public function getBankTransfer()
	{
		return view('Settings.paymentgateways.banktransferForm');
	}

	public function getGoCardless()
	{
		return view('Settings.paymentgateways.gocardlessForm');
	}

	public function postGoCardless(Request $request)
	{

		$testmode = 0;
		if($request->has('testmode'))
		{
			$testmode = 1;
		}

		if(!$request->has('accessToken'))
		{
			return [0,"Must provide your access token."];
		}

		if($request->has('accessToken'))
		{
			$url = 'https://api.gocardless.com';
			if($testmode)
			{
				$url = 'https://api-sandbox.gocardless.com';
			}
			try {
				$guzzle = new Guzzle();
				$response = $guzzle->request('GET', $url.'/customers', [
					'headers' => [
						'Content-type' => 'application/json',
						'Authorization' => 'Bearer '.$request->input('accessToken'),
						'GoCardless-Version' => '2015-07-06'
					]
					//,'body' => json_encode(['limit'=>1])
				]);
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				return [0,"Invalid access token."];
			}
		}

		Settings::set([
			'gocardless.accessToken' => $request->input('accessToken'),
			'gocardless.testmode' => $testmode
		]);
		return [1,'API keys were validated and have been saved.'];
	}

	public function get2Checkout()
	{
		return view('Settings.paymentgateways.2checkoutForm');
	}

	public function post2Checkout(Request $request)
	{
		$this->validate($request, [
			'username' => 'required',
			'password' => 'required',
			'publishablekey' => 'required',
			'privatekey' => 'required',
			'sellerid' => 'required',
			'2CheckoutToken' => 'required'
		], [
			'2CheckoutToken.required' => 'Unable to verify Publishable Key and Seller Id.'
		]);

		$checkout = new TwoCheckout(
			$request->input('username'),
			$request->input('password'),
			$request->input('privatekey'),
			$request->input('sellerid'),
			$request->has('testmode')
		);



			$arrGatewayData = [];

			$url = 'https://www.2checkout.com/checkout/api/1/'.$request->input('sellerid').'/rs/authService';
			if($request->has('testmode'))
			{
				$url = 'https://sandbox.2checkout.com/checkout/api/1/'.$request->input('sellerid').'/rs/authService';
			}

			$arrGatewayData['sellerId'] = $request->input('sellerid');
			$arrGatewayData['privateKey'] = $request->input('privatekey');
			$arrGatewayData['total'] = '0.01';
			$arrGatewayData['currency'] = 'USD';
			$arrGatewayData['merchantOrderId'] = 'BS_TEST_'.time();

			$arrGatewayData['token'] = $request->input('_token');

			$arrGatewayData['name'] = 'BillingServ API Check';

			$arrGatewayData['billingAddr'] = [];
			$arrGatewayData['billingAddr']['addrLine1'] = '109 Vernon House, Friar Lane';
			$arrGatewayData['billingAddr']['addrLine2'] = 'Suite 524';
			$arrGatewayData['billingAddr']['city'] = 'Nottingham';
			$arrGatewayData['billingAddr']['state'] = 'Nottinghamshire';
			$arrGatewayData['billingAddr']['zipCode'] = 'NG1 6DQ';
			$arrGatewayData['billingAddr']['country'] = 'GBR';
			$arrGatewayData['billingAddr']['phoneNumber'] = '03302207048';
			$arrGatewayData['billingAddr']['email'] = 'noreply@billingserv.com';

			//print_r($arrGatewayData);

			try {
				$guzzle = new Guzzle();
				$response = $guzzle->request('POST', $url, [
					'headers' => [
						'Accept'     => 'application/json',
						'Content-type'     => 'application/json'
					],
					'body' => json_encode($arrGatewayData)
				]);
			} catch (\Exception $e) {
				$response = $e->getResponse();
			}
			$response2 = json_decode($response->getBody()->getContents(),1);
			$responseBody = $response->getBody()->getContents();
			//print_r($response2);
			//die();

			$errorCode = intval(json_decode((string) $e->getResponse()->getBody())->exception->errorCode);

			$error = '';
			switch ($errorCode) {
				case 602:
					// This is a credit card failure, the creds are correct.
					break;
				case 300:
					$error = 'Invalid API Username, Password or Private Key';
					break;
				default:
					$error = $e->getMessage();
					break;
			}

			if (!empty($error)) {
				return [0,$error];
			}

		Settings::set([
			'2checkout.username' => $request->input('username'),
			'2checkout.password' => $request->input('password'),
			'2checkout.publishablekey' => $request->input('publishablekey'),
			'2checkout.privatekey' => $request->input('privatekey'),
			'2checkout.sellerid' => $request->input('sellerid'),
			'2checkout.testmode' => $request->has('testmode')
		]);

		return [1,'Account information has been saved.'];
	}

	public function getMerchantFocus()
	{
		if (! (boolean) Settings::get('merchantFocus.submitted', false)) {
			$check = MiscStorage::where('user_id', Auth::User()->id)->where('name', 'merchantFocus')->get();
			if ($check->count() !== 0) {
				foreach ($check->first()->value as $key => $value) {
					Session::flash('_old_input.'.$key, $value);
				}
			}

			return view('Settings.paymentgateways.merchantFocusForm');
		} else {
			return view('Settings.paymentgateways.merchantFocusSubmitted');
		}
	}

	public function postMerchantFocus(Request $request)
	{
		$check = MiscStorage::where('user_id', Auth::User()->id)->where('name', 'merchantFocus');
		if ($check->count() === 0) {
			MiscStorage::create([
				'user_id' => Auth::user()->id,
				'name' => 'merchantfocus',
				'value' => $request->except(['ownerSSN', 'check', 'license', '_token'])
			]);
		} else {
			$savedDetails = $check->first();
			$savedDetails->value = $request->except(['ownerSSN', 'check', 'license', '_token']);
			$savedDetails->save();
		}

		$this->validate($request, [
			'firstName' => 'required',
			'lastName' => 'required',
			'email' => 'required|email',
			'companyName' => 'required',
			'companyAddress' => 'required',
			'companyCity' => 'required',
			'companyState' => 'required',
			'companyZip' => 'required',
			'companyGoods' => 'required',
			'ownerFirstName' => 'required',
			'ownerLastName' => 'required',
			'ownerOwnershipType' => 'required',
			'ownerStreetNumber' => 'required',
			'ownerStreetName' => 'required',
			'ownerState' => 'required',
			'ownerZip' => 'required',
			'ownerPhone' => 'required',
			'ownerSSN' => 'required',
			'ownerBirthday' => 'required',
			'federalTaxId' => 'required',
			'monthlySales' => 'required',
			'averageTicket' => 'required',
			'referenceName' => 'required',
			'routingNumber' => 'required',
			'checkingAccountNumber' => 'required',
			'americanExpressStatus' => 'required',
			'americanExpressNumber' => 'requiredunless:americanExpressStatus,no',
			'check' => 'required',
			'license' => 'required'
		], [
			'ownerSSN.required' => 'The owner SSN field is required.',
			'check.required' => 'You must submit a blank check.',
			'license.required' => 'You must submit your drivers license.'
		]);

		// We need to remove all formating of the phone numbers to split them correctly.
		$phone = preg_replace('/[\D]/', '', $request->input('phone'));
		$ownerPhone = preg_replace('/[\D]/', '', $request->input('ownerPhone'));

		try {
			$guzzle = new Guzzle([
				'base_uri' => Config('api.merchantFocus.url'),
				'headers' => [
					'X-MF-API-TOKEN' => Config('api.merchantFocus.apiToken')
				]
			]);

			$application = Settings::get('merchantFocus');

			if (empty($application)) {
				// Create the application and get the id and key from it.
				$application = $guzzle->post('request.php', [
					'form_params'=> [
						'request' => 'AddAppRequest',
						'json_data' => json_encode([
							'firstName' => $request->input('firstName'),
							'lastName' => $request->input('lastName'),
							'phone' => intval($request->input('phone')),
							'email' => $request->input('email'),
							'varcode' => Config('api.merchantFocus.varcode'),
							'gatewayid' => Config('api.merchantFocus.gateway')
						])
					]
				]);

				if ($this->merchantFocusErrorCheck($application)) {
					$application = json_decode((string) $application->getBody(), true)['AddAppResponse'];
				}

				Settings::set([
					'merchantFocus.id' => $application['id'],
					'merchantFocus.key' => $application['key']
				]);
			}

			// Submit all the information we obtained from the user.
			$this->merchantFocusErrorCheck($guzzle->post('request.php', [
				'form_params' => [
					'request' => 'UpdateAppRequest',
					'json_data' => json_encode([
						'id'  => $application['id'],
						'key' => $application['key'],
						'company' => [
							'DBA'                   => $request->input('companyName'),
							'address'               => $request->input('companyAddress'),
							'city'                  => $request->input('companyCity'),
							'zip'                   => $request->input('companyZip'),
							'email'                 => $request->input('email'),
							'phoneAreaCode'         => substr($phone, 0, 3),
							'phonePrefix'           => substr($phone, 3, 3),
							'phonePostfix'          => substr($phone, 6),
							'typeOfGoodsOrServices' => $request->input('companyGoods')
						],
						'owner' => [
							'ownershipType' => $request->input('ownerOwnershipType'),
							'firstName'     => $request->input('ownerFirstName'),
							'lastName'      => $request->input('ownerLastName'),
							'streetNumber'  => $request->input('ownerStreetNumber'),
							'streetName'    => $request->input('ownerStreetName'),
							'city'          => $request->input('ownerCity'),
							'state'         => $request->input('ownerState'),
							'zip'           => $request->input('ownerZip'),
							'phoneAreaCode' => substr($ownerPhone, 0, 3),
							'phonePrefix'   => substr($ownerPhone, 3, 3),
							'phonePostfix'  => substr($ownerPhone, 6),
							'SSN'           => $request->input('ownerSSN'),
							'birthMonth'    => date('m', strtotime($request->input('ownerBirthday'))),
							'birthDay'      => date('d', strtotime($request->input('ownerBirthday'))),
							'birthYear'     => date('Y', strtotime($request->input('ownerBirthday')))
						],
						'merchant' => [
							'federalTaxID'  => $request->input('federalTaxId'),
							'openDate'      => date('m/d/Y', strtotime($request->input('openDate'))),
							'monthlySales'  => $request->input('monthlySales'),
							'averageTicket' => $request->input('averageTicket'),
							'maximumTicket' => $request->input('maximumTicket')
						],
						'bank' => [
							'checkingAccountNumber' => $request->input('checkingAccountNumber'),
							'routingNumber'         => $request->input('routingNumber'),
							'referenceName'         => $request->input('referenceName')
						],
						'payment' => [
							'americanExpressStatus' => $request->input('americanExpressStatus'),
							'americanExpressNumber' => $request->input('americanExpressNumber'),
							'discoverCardNumber'    => $request->input('discoverCardNumber')
						]
					])
				]
			]));

			// Accept the terms agreement even thought there isn't one?
			$this->merchantFocusErrorCheck($guzzle->post('request.php', [
				'form_params' => [
					'request' => 'AcceptTermsRequest',
					'json_data' => json_encode([
						'id'  => $application['id'],
						'key' => $application['key'],
						'ip_address' => $request->ip()
					])
				]
			]));

			// Submit the application
			$this->merchantFocusErrorCheck($guzzle->post('request.php', [
				'form_params' => [
					'request' => 'SubmitAppRequest',
					'json_data' => json_encode([
						'id'  => $application['id'],
						'key' => $application['key']
					])
				]
			]));

			// Upload Blank Check
			$this->merchantFocusErrorCheck($guzzle->post('request.php', [
				'form_params' => [
					'request' => 'UploadFileRequest',
					'json_data' => json_encode([
						'id'  => $application['id'],
						'key' => $application['key'],
						'fileType' => $request->file('check')->getExtension(),
						'category' => 'check',
						'binData' => urlencode(base64_encode($request->file('check')->openFile()))
					])
				]
			]));

			// Upload Drivers License
			$this->merchantFocusErrorCheck($guzzle->post('request.php', [
				'form_params' => [
					'request' => 'UploadFileRequest',
					'json_data' => json_encode([
						'id'  => $application['id'],
						'key' => $application['key'],
						'fileType' => $request->file('license')->getExtension(),
						'category' => 'license',
						'binData' => urlencode(base64_encode($request->file('license')->openFile()))
					])
				]
			]));

			Settings::set([
				'merchantFocus.submitted' => true
			]);
		} catch (MerchantFocusError $e) {
			return back()->withErrors($e->getErrors())->withInput();
		} catch (\Exception $e) {
			throw $e;
			return back()->withErrors(['An unexpected error occurred.'])->withInput();
		}

		return redirect('/settings/paymentgateways/merchantfocus');
	}

	private function merchantFocusErrorCheck(\GuzzleHttp\Psr7\Response $response)
	{
		$response = json_decode((string) $response->getBody());

		if ($response->status == 'success') {
			return true;
		} else {
			throw new MerchantFocusError($response->errors);
		}
	}

	public function getPYMTPro()
	{
		return view('Settings.paymentgateways.pymtproForm');
	}

	public function postPYMTPro(Request $request)
	{
		$this->validate($request, [
			'token' => 'required',
			'secret' => 'required',
			'coin' => 'required',
			'testmode' => 'required'
		]);

		Settings::set([
			'pymtpro.token' => trim($request->input('token')),
			'pymtpro.secret' => trim($request->input('secret')),
			'pymtpro.testmode' => trim($request->input('testmode')),
			'pymtpro.coin' => trim($request->input('coin'))
		]);

		return [1,'Authentication details saved successfully.'];
	}

	public function postCardinity(Request $request)
	{
        $this->validate($request, [
            'consumer_key' => 'required',
            'consumer_secret' => 'required',
            'currency' => 'required'
        ]);

        Settings::set([
            'cardinity.consumer_key' => $request->consumer_key,
            'cardinity.consumer_secret' => $request->consumer_secret,
            'cardinity.currency' => $request->currency
        ]);

        return [1, trans('backend.settings-cardinity-authorized')];
    }

		public function postBankTransfer(Request $request)
		{
					$this->validate($request, [
							'information' => 'required'
					]);

					Settings::set([
							'banktransfer.information' => $request->information
					]);

					return [1, 'Bank Transfer saved successfully'];
		}

}



class MerchantFocusError extends \Exception {
	private $errors = [];

	public function __construct($errors)
	{
		foreach ($errors as $error) {
			$this->errors[] = $error->message;
		}
	}

	public function getErrors()
	{
		return $this->errors;
	}
}
