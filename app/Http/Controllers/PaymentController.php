<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Mail;
use Session;
use Settings;
use epjwhiz2\Bluepay\Bluepay;
use Illuminate\Http\Request;
use App\Package;
use App\Site;
use App\Transactions;
use App\Counties;
use App\Countries;
use App\Currency;
use App\User_Setting;
use App\SavedPaymentMethods;
use App\Packages\APIs\Payflow;
use App\Mail\GeneralEmail;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use App\Packages\APIs\PayPal;
//use PayPal\Api\Amount;
//use PayPal\Api\Details;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
//use PayPal\Api\Payer;
//use PayPal\Api\PayerInfo;
//use PayPal\Api\Payment;
use PayPal\Api\PaymentCard;
//use PayPal\Api\Transaction;

use App\User;
use Stripe\Stripe;
use Stripe\PaymentMethod;

use Cardinity\Client as CardinityClient;
use Cardinity\Method\Payment as CardinityPayment;
use Cardinity\Exception\InvalidAttributeValue;
use Cardinity\Exception\ValidationFailed;
use Cardinity\Exception\Declined;
use Cardinity\Exception\NotFound;
use Exception;

class PaymentController extends Controller
{
	/**
	 * Gateways that are coded
	 */
	protected $gateways = [
		'paypalpro'      => 'PayPalPro',
		'authorize'      => 'Authorize',
		'bluepay'        => 'BluePay',
		'stripe'         => 'Stripe',
		'paypalstandard' => 'PayPalStandard',
		'offsite.paypalstandard' => 'PayPalStandard',
		'worldpay'       => 'WorldPay',
		'2checkout'      => '2Checkout',
		'gocardless'     => 'GoCardless',
		'cardinity'		   => 'Cardinity',
		'banktransfer'	 => 'BankTransfer'
	];

	/**
	 * Gateways that support cards
	 */
	protected $cardGateways = [
		'paypalpro'      => 'PayPalPro',
		'authorize'      => 'Authorize',
		'bluepay'        => 'BluePay',
		//'stripe'         => 'Stripe',
		'worldpay'       => 'WorldPay',
		'2checkout'      => '2Checkout',
		'cardinity'			 => 'Cardinity'
	];

	/**
	 * Gateways that support bank payment
	 */
	protected $bankGateways = [
		'bluepay'        => 'BluePay',
		//'stripe'         => 'Stripe',
		'gocardless'     => 'GoCardless'
	];

	/**
	 * Gateways that support stripe payment
	 */
	protected $stripeGateways = [
		'stripe'         => 'Stripe'
	];

	/**
	 * Gateways that support bank transfer
	 */
	protected $bankTransferGateways = [
		'banktransfer'   => 'Bank Transfer'
	];

	/**
	 * Gateways that checkout offsite
	 */
	protected $offsiteGateways = [
		'paypalstandard' => 'PayPalStandard',
		'pymtpro' => 'PYMTPro'
	];

	/**
	 * offsiteGateway frontend Nice Name for templates?
	 * Should be handled by lang stuff
	 */
	protected $offsiteGatewaysNiceNames = [
		'paypalstandard' => 'PayPal',
		'pymtpro' => 'PYMT'
	];

	/**
	 * Gateways that use tokens to handle cc data
	 */
	protected $tokenPaymentGateways = [
		'stripe'         => 'Stripe',
		'worldpay'       => 'WorldPay',
		'2checkout'      => '2Checkout',
		'bluepay'      => 'BluePay',
	];

	/**
	 * gateways that allow saving of the used payment method.
	 * Do saved methods even work?
	 */
	protected $savePaymentMethodGateways = [
		'stripe'         => 'Stripe',
		'bluepay'        => 'BluePay',
		'gocardless'     => 'GoCardless'
	];

	public function getAvailablePaymentMethods()
	{
		$data = [
			'offsite' => [],
			'card' => 0,
			'stripe' => 0,
			'bank' => 0
		];

		$defaultGateway = Controller::site('defaultGateway');
		if (!empty($defaultGateway)) {
			if (array_key_exists($defaultGateway, $this->cardGateways)) {
				$data['card'] = 1;
				$data['cardGateway'] = $defaultGateway;
			}

			if (array_key_exists($defaultGateway, $this->bankGateways)) {
				$data['bank'] = 1;
				$data['bankGateway'] = $defaultGateway;
			}

			if (array_key_exists($defaultGateway, $this->stripeGateways)) {
				$data['stripe'] = 1;
				$data['stripeGateway'] = $defaultGateway;
			}

			if (array_key_exists($defaultGateway, $this->bankTransferGateways)) {
				$data['banktransfer'] = 1;
				$data['banktransferGateway'] = $defaultGateway;
			}
		}

		$data['offsite'] = $this->getOffsite();
		$data['checked'] = $this->getActiveTab($data);

		return $data;
	}

	/**
	 * Get active tab
	 *
	 * @param array $data
	 * @return string
	 */
	public function getActiveTab($data)
	{
		if ($data['card'] === 1) {
			return 'card';
		}

		if ($data['bank'] === 1) {
			return 'bank';
		}

		if ($data['stripe'] === 1) {
			return 'stripe';
		}

		return 'offsite';
	}

	public function getOffsite()
	{
		$enabledGateways = DB::table('user_settings')
			->select('name', 'value')
			->where('user_id', '1') //self::siteModal()->id)
			->where('name','LIKE','gateway.enabled.%')->get();

		if (!$enabledGateways) {
			return null;
		}

		$offsite = [];

		foreach($enabledGateways as $gateway) {
			$tempGateway = explode('.',$gateway->name);
			$tempGateway = sizeof($tempGateway) == 3 ? end($tempGateway) : [];

			if (array_key_exists($tempGateway, $this->offsiteGateways)) {
				$offsite[] = (object) [
					'name' => $tempGateway,
					'nice_name' => $this->offsiteGatewaysNiceNames[$tempGateway],
				];
			}
		}

		return $offsite;
	}

	public function getGatewayTokenStatus()
	{
		$gateway = Controller::site('defaultGateway');

		if (empty($gateway)) {
			$gateway = '';
		}

		$currency = Controller::setCurrency();
		if (array_key_exists($gateway,$this->tokenPaymentGateways) && $this->user) {
			if ($gateway == 'stripe') {
				$publicKey = $this->user->getSetting('stripe.publishablekey');
				$vars = [
					'stripePublicKey' => $publicKey
				];

				if (!empty($customer->stripeId)) {
					\Stripe\Stripe::setApiKey($this->user->getSetting('stripe.secretkey'));
					$stripeCustomer = \Stripe\Customer::retrieve($customer->stripeId);
					if ($stripeCustomer->sources->total_count > 0) {
						$vars['savedPayments'] = $stripeCustomer->sources->data;
					}
				}

				$vars2 = [
					'tokenDataCCNumber' => 'data-stripe="number"',
					'tokenDataCCCvc' => 'data-stripe="cvc"',
					'tokenDataExpMonth' => 'data-stripe="exp_month"',
					'tokenDataExpYear' => 'data-stripe="exp_year"',
					'tokenDataCCName' => 'data-stripe="name"'
				];
			} elseif ($gateway == 'bluepay') {

				$accId = $this->user->getSetting('bluepay.account_id');
				$secret = $this->user->getSetting('bluepay.secretkey');
				$testmode = $this->user->getSetting('bluepay.testmode');

				if (empty($accId) || empty($secret) || empty($testmode)) {
					$error = 'Missing Bluepay account details.';
					//abort(500);
				}

				$apiSig = md5($secret . $accId);

				$vars = [
					'bluepayAccId'=>$accId,
					'bluepayApiSignature'=>$apiSig,
					'bluepayTestMode'=>$testmode
				];

				$vars2 = [
					'tokenDataCCNumber' => 'data-bluepay="number"',
					'tokenDataCCCvc' => 'data-bluepay="cvc"',
					'tokenDataExpMonth' => 'data-bluepay="exp_month"',
					'tokenDataExpYear' => 'data-bluepay="exp_year"',
					'tokenDataCCName' => 'data-bluepay="name"'
				];
			} elseif ($gateway == 'worldpay') {
				$worldpay = self::siteModal()->settings()->where('name', 'LIKE', 'worldpay.%')->get()->pluck('value', 'name')->all();
				$vars = [];
				$vars['testmode'] = $worldpay['worldpay.testmode'];

				if ($vars['testmode']) {
					$vars['worldpaykey'] = $worldpay['worldpay.testClientKey'];
				} else {
					$vars['worldpaykey'] = $worldpay['worldpay.liveClientKey'];
				}

				$vars2 = [
					'tokenDataCCNumber' => 'data-worldpay="number"',
					'tokenDataCCCvc' => 'data-worldpay="cvc"',
					'tokenDataExpMonth' => 'data-worldpay="exp-month"',
					'tokenDataExpYear' => 'data-worldpay="exp-year"',
					'tokenDataCCName' => 'data-worldpay="name"'
				];
			} elseif ($gateway == '2checkout') {

				$keys = self::siteModal()->settings()->where('name', 'LIKE', '2checkout.%')->get()->pluck('value', 'name')->all();
				$vars = [];
				$vars['sellerid'] = $keys['2checkout.sellerid'];
				$vars['publishablekey'] = $keys['2checkout.publishablekey'];
				$vars['testmode'] = $keys['2checkout.testmode'];
				$vars2 = [
					'tokenDataCCNumber' => 'data-2checkout="number"',
					'tokenDataCCCvc' => 'data-2checkout="cvc"',
					'tokenDataExpMonth' => 'data-2checkout="exp-month"',
					'tokenDataExpYear' => 'data-2checkout="exp-year"',
					'tokenDataCCName' => 'data-2checkout="name"'
				];
			}

			$vars['gatewayName'] = $gateway;

			return [1,$vars,$vars2];
		}

		return [
			0,
			null,
		];
	}

	public function getOffsiteGateways()
	{

	}

	public function pay(Request $request = null)
	{
		
		$currency = Controller::setCurrency();
		$defGateway = Controller::site('defaultGateway');
		
		$payMethod = $request->paymentMethod['type'];

		if($payMethod != 'offsite.paypalstandard'){
			if ($defGateway == false) {
				throw new \Exception('Payment gateway not set');
			}
		}

		$forceRedirect = false;
		$objResponse = json_decode($request->transaction_json);
		/**
		 * log transaction
		 * trans id, invoice id, user id, customer id, gateway id
		 * amount, payment method, status
		 * json_encoded raw response,message
		 */
		$transaction = new Transactions();
		if ($request && $request->transaction_id) {
			$transaction->transaction_id = $request->transaction_id;
		} else {
			$transaction->transaction_id = $this->invoice->id . '.' . microtime();
		}
		$transaction->invoice_id = $this->invoice->id;
		$transaction->user_id = 1;
		$transaction->customer_id = $this->invoice->customer_id;
		$transaction->amount = $this->invoice->total;
		$transaction->payment_method = $request->paymentMethod['type'];

		if(isset($objResponse->paymentIntent)) {
			 if($objResponse->paymentIntent->status == 'succeeded') {
				$transaction->status = 1;
				$this->invoice->status  = 2;
				$this->invoice->save();
			}
		}
		else if(isset($objResponse->status)) {
		 				if($objResponse->status == 'succeeded') {
								$transaction->status = 1;
								$this->invoice->status  = 2;
								$this->invoice->save();
				 		}
		}
		else if(isset($request->transaction_status)) {
		 				if($request->transaction_status == 'succeeded') {
								$transaction->status = 1;
								$this->invoice->status  = 2;
								$this->invoice->save();
				 		}
		}
		else {
			$transaction->status = 0;
		}
		if($request && $request->transaction_json) {
			$transaction->json_response = $request->transaction_json;
		}
		else {
			$transaction->json_response = '';
		}
		$transaction->message = '';
		$transaction->currency_id = $currency->id;
		$transaction->gateway_id = '';

		$transKey = base64_encode($this->invoice->id . $this->invoice->customer_id . $this->invoice->total . $this->invoice->created_at);
		$transaction->transaction_key = $transKey;

		$transaction->save();

		session()->put('receipt.transaction_id',$transaction->id);
		$this->transaction = $transaction;

		$package = Package::find($request->package_id);
		$this->package = $package;

		if ($this->invoice->total > 0) {
			if (session()->has('cart.totalDiscounts') && session()->get('cart.totalDiscounts') > 0)  {

				/**
				 * DONT SAVE THIS AMOUNT EASY HACK TO CHANGE
				 * AMOUNT CHARGED ON GATEWAYS
				 */
				$this->invoice->total = round($this->invoice->total - session()->get('cart.totalDiscounts') * 100) / 100;
			} elseif (isset($this->fixedDiscount) && $this->fixedDiscount > 0) {

				/**
				 * DONT SAVE THIS AMOUNT EASY HACK TO CHANGE
				 * AMOUNT CHARGED ON GATEWAYS
				 */
				$this->invoice->total = round($this->invoice->total - $this->fixedDiscount * 100) / 100;
			}

			/**
			 * charge extra to cover exchange rates
			 */
			if (Settings::get('invoice.exchangerate',0) && $this->invoice->currency_id != Settings::get('site.defaultCurrency',4))  {

				/**
				 * DONT SAVE THIS AMOUNT
				 * THIS IS EASY HACK TO CHANGE AMOUNT CHARGED ON GATEWAYS
				 */
				$this->invoice->total = round($this->invoice->total + $this->invoice->total * Settings::get('invoice.exchangerate') / 100 * 100) / 100;
			}

			switch ($this->paymentMethod['type']) {
				case '0':
					/**
					 * Process cc
					 */
					if($defGateway != 'stripe') {
						$result = $this->{'payGateway'.$defGateway}();
						$transaction->gateway_id = $defGateway;
					}
				break;
				case '1':
					/**
					 * Process bank
					 */
					$result = $this->{'payGateway'.$defGateway}();
					$transaction->gateway_id = $defGateway;
					$transaction->save();
				break;
				default:
					/**
					 * Process offsite gateway
					 */
					$payMethod = explode('offsite.',$this->paymentMethod['type']);
					if (isset($payMethod[1]) && isset($this->offsiteGateways[$payMethod[1]])) {

						$transaction->gateway_id = $this->offsiteGateways[$payMethod[1]];
						$transaction->save();
						return $this->{'payOffsiteGateway'.$this->offsiteGateways[$payMethod[1]]}();
					} else {
						/**
						 * Jordan wants no gateway to still create invoice.
						 */
						$forceRedirect = true;

						$result = [
							0,
							$this->invoice->id,
							$this->invoice->user->id,
							$this->invoice->customer->id,
							'none',
							$this->invoice->total,
							'',
							0,
							'',
							'',
							''
						];
					}
				break;
			}
		} else {
			$transaction->gateway_id = '';
			$transaction->save();

			$result = [
				0,
				$this->invoice->id,
				$this->invoice->user->id,
				$this->invoice->customer->id,
				'none',
				$this->invoice->total,
				'',
				1,
				'',
				'free'
			];
		}

		/**
		 * log transaction
		 * trans id, invoice id, user id, customer id,
		 * gateway id, amount, payment method, status,
		 * json_encoded raw response,message
		 */

		if($defGateway != 'stripe') {
            $transaction->transaction_id = $result[0];
            $transaction->payment_method = $result[6];
            if($transaction->status != 1) $transaction->status = $result[7];
            $transaction->json_response = $result[4] === 'cardinity' ? $result[8] : json_encode($result[10]);
            $transaction->message = (string) $result[9];
            $transaction->save();
		}
		$redirect = $transaction->status;

		if ($forceRedirect) {
			$redirect = 1;
		}

		return [$redirect, $transaction->id, $transaction->message, $transaction->status, $defGateway];
	}

	private function payOffsiteGatewayPYMTPro()
	{
		$token = $this->user->getSetting('pymtpro.token');
		$secret = $this->user->getSetting('pymtpro.secret');
		$coin = $this->user->getSetting('pymtpro.coin');
		$testmode = $this->user->getSetting('pymtpro.testmode');
		$domain = 'pymtpro.com/';

		if ($testmode) {
			$domain = 'sandbox.pymtpro.com/';
		}

		$url = 'https://api.'.$domain . 'v1/order/new/?api_token=' . $token . '&api_secret=' . $secret;

		try {
			$custom = urlencode(hash('sha256',$this->invoice->customer->id . $this->invoice->customer->email . $this->invoice->created_at . $this->invoice->customer->password . $this->invoice->id));

			$guzzle = new Guzzle();
			$data = [
				'headers' => [
					'Content-type' => 'application/json'
				],
				'body' => json_encode([
					'button' => [
						'name' => $this->invoice->invoice_number,
						'coin' => $coin,
						'style' => '',
						'description' => '',
						'custom' => $custom,
						'callback_url' => 'https://'.Config('app.site')->domain.'/webhooks/pymt/callback/'.$this->invoice->id,
						'success_url' => 'https://'.Config('app.site')->domain.'/receipt?transId=' . $this->transaction->id . '&transKey=' . $this->transaction->transaction_key,
						'cancel_url' => 'https://'.Config('app.site')->domain.'/viewcart',
						'price_string' => $this->invoice->total,
						'price_currency_iso' => 'usd'
					]
				])
			];

			$response = $guzzle->request('POST', $url, $data);

			$response2 = json_decode($response->getBody()->getContents(),1);

			if (!empty($response2) && isset($response2['success']) && $response2['success'] === 'true') {
				$data = json_encode([
					'offsiteGateway'=>'<FORM id="frmOffsiteGateway" ACTION="'.'https://'.$domain.'order/' . $response2['button']['code'] . '" METHOD="GET"></FORM>'
				]);
				header('Content-type: application/json');
				echo $data;
				die();
			} elseif(!empty($response2)) {
				$error = $response2['error']['message'];
			}
		} catch(\Exception $e) {
			$error = $e->getMessage();
		}

		echo json_encode([
			'errors' => [
				$error
			]
		]);
		die();
	}

	private function payOffsiteGatewayPayPalStandard()
	{
		if($this->user->getSetting('paypalstandard.testmode'))
		{
			$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}
		else
		{
			$url = 'https://www.paypal.com/cgi-bin/webscr';
		}
		$region = $this->request->input('billingAddress.region');
		$country = substr($this->request->input('billingAddress.country'),0,64);
		header('Content-type: application/json');
		die(json_encode(['offsiteGateway'=>'<FORM id="frmOffsiteGateway" ACTION="'.$url.'" METHOD="POST">
		<INPUT TYPE="hidden" NAME="cmd" VALUE="_xclick">
		<INPUT TYPE="hidden" NAME="business" VALUE="'.$this->user->getSetting('paypalstandard.email').'">
		<INPUT TYPE="hidden" NAME="no_shipping" VALUE="1">
		<INPUT TYPE="hidden" NAME="rm" VALUE="2">
		<INPUT TYPE="hidden" NAME="item_name" VALUE="Invoice Number: '.$this->invoice->invoice_number.'">
		<INPUT TYPE="hidden" NAME="amount" VALUE="'.$this->invoice->total.'">
		<INPUT TYPE="hidden" NAME="currency_code" VALUE="'.$this->currency->short_name.'">
		<INPUT TYPE="hidden" NAME="first_name" VALUE="'.$this->request->input('billingInfo.firstname').'">
		<INPUT TYPE="hidden" NAME="last_name" VALUE="'.$this->request->input('billingInfo.lastname').'">
		<INPUT TYPE="hidden" NAME="address1" VALUE="'.$this->request->input('billingAddress.address1').'">
		<INPUT TYPE="hidden" NAME="address2" VALUE="'.$this->request->input('billingAddress.address2').'">
		<INPUT TYPE="hidden" NAME="city" VALUE="'.$this->request->input('billingAddress.city').'">
		<INPUT TYPE="hidden" NAME="state" VALUE="'.Counties::findOrFail($region)->name.'">
		<INPUT TYPE="hidden" NAME="zip" VALUE="'.$this->request->input('billingAddress.zip').'">
		<INPUT TYPE="hidden" NAME="lc" VALUE="'.Countries::findOrFail($country)->iso2.'">
		<INPUT TYPE="hidden" NAME="email" VALUE="'.$this->request->input('billingInfo.email').'">
		<INPUT TYPE="hidden" NAME="notify_url" VALUE="https://'.Config('app.site')->domain.'/webhooks/paypal/ipn/'.$this->invoice->id.'?txn=' . $this->transaction->id . '">
		<INPUT TYPE="hidden" NAME="return" VALUE="https://' . Config('app.site')->domain . '/checkout/receipt?transId=' . $this->transaction->id . '&transKey=' . $this->transaction->transaction_key . '">
		<INPUT TYPE="hidden" NAME="cancel_return" VALUE="https://'.Config('app.site')->domain.'/viewcart">
		</FORM>']));

	}
	private function payGatewayBluePay()
	{
			$bluepay = new Bluepay(
				$this->user->getSetting('bluepay.account_id'),
				$this->user->getSetting('bluepay.secretkey'),
				$this->user->getSetting('bluepay.testmode')
			);

			$amount = $this->invoice->total;

			if(isset($this->savedPaymentMethod))
			{
				$bluepay->setToken($this->savedPaymentMethod->token);
			}
			elseif(isset($this->paymentMethod['token']) && !empty($this->paymentMethod['token']))
			{
				$bluepay->setToken($this->paymentMethod['token']);
			}
			elseif($this->paymentMethod['type'] == 0)
			{
				//cc
				$cardNo = $this->paymentMethod['number'];
				$cv2 = $this->paymentMethod['cvc'];
				$month = $this->paymentMethod['expiration']['month'];
				if($month < 10)
				{
					$month = '0'.$month;
				}
				$year = $this->paymentMethod['expiration']['year'];
				if($year < 10)
				{
					$year = '0'.$year;
				}
				$exp =  $month . $year;

				if(empty($cardNo))
				{
					abort(500,'Missing Card Number.');
				}
				if(empty($cv2))
				{
					abort(500,'Missing Card CVC.');
				}
				if(empty($exp))
				{
					abort(500,'Missing Card Expiration.');
				}

				$bluepay->setCreditCard($cardNo,$cv2,$exp);

			}
			elseif($this->paymentMethod['type'] == 1)
			{
				//ach
				$type = $this->paymentMethod['bank_type'];
				$account = $this->paymentMethod['account'];
				$routing = $this->paymentMethod['routing'];

				if($type == 0)
				{
					$type = 'C';
				}
				else
				{
					$type = 'S';
				}
				$bluepay->setACH($routing,$account,$type,'');
			}
			$bluepay->setAmount($amount,0,0);

			if(isset($this->billingAddress->firstname))
			{
				$name = substr($this->billingAddress->firstname,0,32);
				$name2 = substr($this->billingAddress->lastname,0,32);
			}
			else
			{
				$name = $this->billingAddress->firstname;
				$name2 = $this->billingAddress->lastname;
			}
			$address = substr($this->billingAddress->address_1,0,64);
			$address2 = substr($this->billingAddress->address_2,0,64);
			$city = substr($this->billingAddress->city,0,32);
			$region = $this->billingAddress->county_id;

			$state = substr(Counties::findOrFail($region)->name,0,32);
			$zip = substr($this->billingAddress->postal_code,0,16);
			$phone = substr($this->billingAddress->phone,0,16);
			$email = substr($this->billingAddress->email,0,64);
			$countryId = $this->billingAddress->country_id;
			$country = substr(Countries::findOrFail($countryId)->name,0,64);

			$ip = '';
			if(isset($_SERVER) && isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']))
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}


			$bluepay->setCustomerDetails($name, $name2, $address, $address2, $city, $state, $zip, $phone, $email, $country, $ip);
			$bluepay->setDuplicatesAllowed(1);

			$response = $bluepay->process('SALE');

			if($response['STATUS'] == '1' && $response['MESSAGE'] != 'DUPLICATE')
			{
				//success
				$status = 1;
			}
			elseif($response['STATUS'] == '0')
			{
				//declined
				$status = 0;
			}
			else
			{
				//error
				$status = 0;
			}

			if((((isset($this->paymentMethod['ccsave']) && $this->paymentMethod['ccsave'] == 1) || (isset($this->paymentMethod['banksave'])) && $this->paymentMethod['banksave'] == 1) && !isset($this->savedPaymentMethod) && $status == 1) && empty($this->savedPaymentMethod))
			{
				$default = '';
				if((isset($this->paymentMethod['ccautocharge']) && !empty($this->paymentMethod['ccautocharge'])) || (isset($this->paymentMethod['bankautocharge']) && !empty($this->paymentMethod['bankautocharge'])))
				{
					SavedPaymentMethods::where('default','default')->where('user_id',$this->customer->id)->where('type',$this->paymentMethod['type'])->where('gateway_id','bluepay')->update(['default'=>'']);
					$default = 'default';
				}

				if($this->paymentMethod['type'] == 0)
				{
					$expMonth = '';
					if(isset($this->paymentMethod['expMonth']))
					{
						$expMonth = substr($this->paymentMethod['expMonth'],0,2);
					}

					$expYear = '';
					if(isset($this->paymentMethod['expYear']))
					{
						$expYear = substr($this->paymentMethod['expYear'],0,2);
					}

					$last4 = '';
					if(isset($this->paymentMethod['last4']))
					{
						$last4 = $this->paymentMethod['last4'];
					}

					$cardType = '';

					$method = new SavedPaymentMethods();
					$method->type = $this->paymentMethod['type'];
					$method->user_id = $this->customer->id;
					$method->gateway_id = 'bluepay';
					$method->billing_address_id = $this->billingAddress->id;
					$method->card_type = $cardType;
					$method->last4 = $last4;
					$method->expiration_month = $expMonth;
					$method->expiration_year = '20' . $expYear;
					$method->token = $response['TRANS_ID'];
					$method->default = $default;
					$method->save();
				}
				elseif($this->paymentMethod['type'] == 1)
				{
					$expMonth = '';

					$expYear = '';

					$cardType = '';

					$last4 = '';
					if(isset($this->paymentMethod['last4']))
					{
						$last4 = $this->paymentMethod['last4'];
					}

					$method = new SavedPaymentMethods();
					$method->type = $this->paymentMethod['type'];
					$method->user_id = $this->customer->id;
					$method->gateway_id = 'bluepay';
					$method->billing_address_id = $this->billingAddress->id;
					$method->card_type = $cardType;
					$method->last4 = $last4;
					$method->expiration_month = $expMonth;
					$method->expiration_year = $expYear;
					$method->token = $response['TRANS_ID'];
					$method->default = $default;
					$method->save();
				}
			}

			session()->put('payment_status', $status);
			session()->put('payment_message', $response['MESSAGE']);

			//declined or error
			if($status == 0)
			{
					$userFrom = $this->user;
					$subject = $this->user->siteSettings('name') . ' Payment Declined payGatewayBluePay';
					$content = '--content payment declined--';
					$view = 'Checkout.mail.paymentErrorEmail';
					Mail::to($this->invoice->customer)->send(new GeneralEmail($userFrom, $subject, $content, $view));
			}

			return [$response['TRANS_ID'],$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->id,'bluepay',$amount,$this->paymentMethod['type'],$status,$response,$response['MESSAGE']];
	}

	private function payGateway2Checkout()
	{
			$keys = self::siteModal()->settings()->where('name', 'LIKE', '2checkout.%')->get()->pluck('value', 'name')->all();
			$testmode = $keys['2checkout.testmode'];

			if(!is_object($this->request))
			{
				abort(500,'Invalid request object.');
			}

			$arrGatewayData = [];

			$url = 'https://www.2checkout.com/checkout/api/1/'.$keys['2checkout.sellerid'].'/rs/authService';
			if($keys['2checkout.testmode'])
			{
				$url = 'https://sandbox.2checkout.com/checkout/api/1/'.$keys['2checkout.sellerid'].'/rs/authService';
			}

			$arrGatewayData['sellerId'] = $keys['2checkout.sellerid'];
			$arrGatewayData['privateKey'] = $keys['2checkout.privatekey'];
			$arrGatewayData['total'] = $this->invoice->total;
			$arrGatewayData['currency'] = $this->currency->short_name;
			$arrGatewayData['orderDescription'] = $this->invoice->invoice_number;
			$arrGatewayData['merchantOrderId'] = $this->invoice->id;

			$arrGatewayData['token'] = $this->request->input('2checkoutToken');

			$arrGatewayData['name'] = substr($this->request->input('billingInfo.firstname') . ' ' . $this->request->input('billingInfo.lastname'),0,128);

			$arrGatewayData['billingAddr'] = [];
			$arrGatewayData['billingAddr']['addrLine1'] = substr($this->request->input('billingAddress.address1'),0,64);
			$arrGatewayData['billingAddr']['addrLine2'] = substr($this->request->input('billingAddress.address2'),0,64);
			$arrGatewayData['billingAddr']['city'] = $this->request->input('billingAddress.city');
			$region = $this->request->input('billingAddress.region');
			$arrGatewayData['billingAddr']['state'] = Counties::findOrFail($region)->name;
			$arrGatewayData['billingAddr']['zip'] = substr($this->request->input('billingAddress.zip'),0,16);
			$country = $this->request->input('billingAddress.country');
			$arrGatewayData['billingAddr']['country'] = Countries::findOrFail($country)->name;
			$arrGatewayData['billingAddr']['phoneNumber'] = substr($this->request->input('billingInfo.phone'),0,16);
			$arrGatewayData['billingAddr']['email'] = trim($this->request->input('billingInfo.email'));

			try {
				$guzzle = new Guzzle();
				$response = $guzzle->request('POST', $url, [
					'headers' => [
						'Content-type'     => 'application/json'
					],
					'body' => json_encode($arrGatewayData)
				]);
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				 $response = $e->getResponse();
			}
			$response2 = json_decode($response->getBody()->getContents(),1);
			$responseBody = $response->getBody()->getContents();
			if(isset($response2['errorCode']) || isset($response2['errorMsg']))
			{
				//error
				$status = 0;
				$responseMessage = $response2['errorMsg'];
				$transId = 'error';
			}
			elseif(isset($response2['responseCode']) && $response2['responseCode'] === 'APPROVED')
			{
				//success
				$status = 1;
				$responseMessage = $response2['responseMsg'];
				$transId = $response2['transactionId'];
			}

			session()->put('payment_status', $status);
			session()->put('payment_message', $responseMessage);

			//declined or error
			if($status == 0)
			{
					$userFrom = $this->user;
					$subject = $this->user->siteSettings('name') . ' Payment Declined PayGateway2Checkout';
					$content = '--content payment declined--';
					$view = 'Checkout.mail.paymentErrorEmail';
					Mail::to($this->invoice->customer)->send(new GeneralEmail($userFrom, $subject, $content, $view));
			}

			return [$transId,$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->id,'worldpay',$this->invoice->total,$this->request->input('paymentMethod.type'),$status,$response2,$responseMessage];
	}

	private function payGatewayWorldPay()
	{
			$keys = self::siteModal()->settings()->where('name', 'LIKE', 'worldpay.%')->get()->pluck('value', 'name')->all();
			$testmode = $keys['worldpay.testmode'];
			$key = $keys['worldpay.liveServiceKey'];
			if($testmode)
			{
				$key = $keys['worldpay.testServiceKey'];
			}


			if(!is_object($this->request))
			{
				abort(500,'Invalid request object.');
			}

			$arrGatewayData = [];

			$arrGatewayData['orderType'] = 'ECOM';
			$arrGatewayData['amount'] = $this->invoice->total * 100;
			$arrGatewayData['currencyCode'] = $this->currency->short_name;
			$arrGatewayData['orderDescription'] = $this->invoice->invoice_number;
			$arrGatewayData['customerOrderCode'] = $this->invoice->id;

			$arrGatewayData['environment'] = 'LIVE';
			if($testmode)
			{
				$arrGatewayData['environment'] = 'TEST';
			}
			if(!$this->request->has('worldpayToken'))
			{
				die('missing token');
			}

			$arrGatewayData['token'] = $this->request->input('worldpayToken');

			$arrGatewayData['name'] = $this->request->input('billingInfo.firstname') . ' ' . $this->request->input('billingInfo.lastname');

			$arrGatewayData['billingAddress'] = [];
			$arrGatewayData['billingAddress']['address1'] = substr($this->request->input('billingAddress.address1'),0,64);
			$arrGatewayData['billingAddress']['address2'] = substr($this->request->input('billingAddress.address2'),0,64);
			$arrGatewayData['billingAddress']['address3'] = substr($this->request->input('billingAddress.address3'),0,64);
			$arrGatewayData['billingAddress']['city'] = $this->request->input('billingAddress.city');
			$region = $this->request->input('billingAddress.region');
			$arrGatewayData['billingAddress']['state'] = Counties::findOrFail($region)->name;
			$arrGatewayData['billingAddress']['postalCode'] = substr($this->request->input('billingAddress.zip'),0,16);
			$country = $this->request->input('billingAddress.country');
			$arrGatewayData['billingAddress']['countryCode'] = Countries::findOrFail($country)->iso2;
			$arrGatewayData['billingAddress']['telephoneNumber'] = substr($this->request->input('billingInfo.phone'),0,16);

			$arrGatewayData['shopperEmailAddress'] = trim($this->request->input('billingInfo.email'));
			$arrGatewayData['shopperIpAddress'] = $_SERVER['REMOTE_ADDR'];

			try {
				$guzzle = new Guzzle();
				$response = $guzzle->request('POST', 'https://api.worldpay.com/v1/orders', [
					'headers' => [
						'Authorization' => $key,
						'Content-type'     => 'application/json'
					],
					'body' => json_encode($arrGatewayData)
				]);
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				 $response = $e->getResponse();
			}
			$response2 = json_decode($response->getBody()->getContents(),1);
			$responseBody = $response->getBody()->getContents();
			if(isset($response2['paymentStatus']) && $response2['paymentStatus'] === 'SUCCESS')
			{
				//success
				$status = 1;
				$responseMessage = 'Payment Success';
				$orderCode = $response2['orderCode'];
			}
			else
			{
				//error
				$status = 0;
				$responseMessage = $response2['description'];
				$orderCode = 'none';
			}

			session()->put('payment_status', $status);
			session()->put('payment_message', $responseMessage);

			//declined or error
			if($status == 0)
			{
					$userFrom = $this->user;
					$subject = $this->user->siteSettings('name') . ' Payment Declined payGatewayWorldPay';
					$content = '--content payment declined--';
					$view = 'Checkout.mail.paymentErrorEmail';
					Mail::to($this->invoice->customer)->send(new GeneralEmail($userFrom, $subject, $content, $view));
			}

			return [$orderCode,$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->id,'worldpay',$this->invoice->total,$this->request->input('paymentMethod.type'),$status,$response2,$responseMessage];
	}

	private function payGatewayGoCardless()
	{
			$keys = self::siteModal()->settings()->where('name', 'LIKE', 'gocardless.%')->get()->pluck('value', 'name')->all();
			$testmode = $keys['gocardless.testmode'];
			$key = $keys['gocardless.accessToken'];

			$url = 'https://api.gocardless.com';
			if($testmode)
			{
				$url = 'https://api-sandbox.gocardless.com';
			}

			if(!is_object($this->request))
			{
				abort(500,'Invalid request object.');
			}
			$country = $this->request->input('billingAddress.country');
			$region = $this->request->input('billingAddress.region');
			$rawResponse = [];

			//check if customer exists
			$customerGoCardless = $this->invoice->customer()->first()->settings()->where('name', 'LIKE', 'gocardless.%')->get()->pluck('value', 'name')->all();
			if(isset($customerId['gocardless.customerId']))
			{
				$customerId = $customerId['gocardless.customerId'];
			}
			else
			{
				//create customer
				try {
					$guzzle = new Guzzle();
					$response = $guzzle->request('POST', $url.'/customers', [
						'headers' => [
							'Content-type' => 'application/json',
							'Authorization' => 'Bearer '.$key,
							'GoCardless-Version' => '2015-07-06'
						],
						'body' => json_encode([
							'customers'=>[
							'address_line1'=>$this->request->input('billingAddress.address1'),
							'address_line2'=>$this->request->input('billingAddress.address2'),
							'address_line3'=>$this->request->input('billingAddress.address3'),
							'city'=>$this->request->input('billingAddress.city'),
							'company_name'=>$this->request->input('billingInfo.company'),
							'country_code'=>Countries::findOrFail($country)->iso2,
							'email'=>$this->request->input('billingInfo.email'),
							'family_name'=>$this->request->input('billingInfo.lastname'),
							'given_name'=>$this->request->input('billingInfo.firstname'),
							'metadata'=>['customerId'=>(string)$this->invoice->customer()->first()->id],
							'postal_code'=>$this->request->input('billingAddress.zip'),
							'region'=>Counties::findOrFail($region)->name
							]
						])
					]);
				} catch (\GuzzleHttp\Exception\ClientException $e) {
					 $response = $e->getResponse();
				}
				$contents = $response->getBody()->getContents();
				$rawResponse[] = $contents;
				$responseArr = json_decode($contents,1);
				if($response->getStatusCode() == '201')
				{
					$customerId = $responseArr['customers']['id'];
				}
				else
				{
					return [$responseArr['request_id'],$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->first()->id,'gocardless',$this->invoice->total,$this->request->input('paymentMethod.type'),0,$rawResponse,$responseArr['message']];
				}
			}

			//check if bank account exists or create
			$bankAccount = null;
			if($this->request->has('savedPaymentMethod'))
			{
				$paymentMethod = $this->getUsersSavedPaymentMethod($this->request->input('savedPaymentMethod'));
				try {
					$guzzle = new Guzzle();
					$response = $guzzle->request('GET', $url.'/customer_bank_accounts/'.$paymentMethod->custom1, [
						'headers' => [
							'Content-type' => 'application/json',
							'Authorization' => 'Bearer '.$key,
							'GoCardless-Version' => '2015-07-06'
						]
					]);
				} catch (\GuzzleHttp\Exception\ClientException $e) {
					 $response = $e->getResponse();
				}
				$contents = $response->getBody()->getContents();
				$rawResponse[] = $contents;
				$responseArr = json_decode($contents,1);
				if($response->getStatusCode() == '200')
				{
					$bankAccount = $paymentMethod->custom1;
				}
				//THIS PART IS BROKEN FOR SURE
				elseif(isset($responseArr['links[customer_bank_account]']))
				{
					$bankAccount = $responseArr['links[customer_bank_account]'];
				}
			}

			if(empty($bankAccount))
			{
				//create bank account
				$body = ['customer_bank_accounts'=>[
							'account_holder_name'=>substr($this->request->input('billingInfo.firstname') . ' ' . $this->request->input('billingInfo.lastname'),0,18),
							'currency'=>$this->currency->short_name,
							'metadata'=>['customerId'=>(string)$this->invoice->customer()->first()->id],
							'links'=>['customer'=>$customerId]
						]];
				if($this->request->has('paymentMethod.iban') && !empty($this->request->input('paymentMethod.iban')))
				{
					$body['IBAN'] = $this->request->input('paymentMethod.iban');
				}
				else
				{
					if($this->request->has('paymentMethod.account') && !empty($this->request->input('paymentMethod.account')))
					{
						$body['customer_bank_accounts']['account_number'] = $this->request->input('paymentMethod.account');
					}
					if($this->request->has('paymentMethod.bankCode') && !empty($this->request->input('paymentMethod.bankCode')))
					{
						$body['customer_bank_accounts']['bank_code'] = $this->request->input('paymentMethod.bankCode');
					}
					if($this->request->has('paymentMethod.branchCode') && !empty($this->request->input('paymentMethod.branchCode')))
					{
						$body['customer_bank_accounts']['branch_code'] = $this->request->input('paymentMethod.branchCode');
					}
					$body['customer_bank_accounts']['country_code'] = Countries::findOrFail($country)->iso2;
				}
				try {
					$guzzle = new Guzzle();
					$response = $guzzle->request('POST', $url.'/customer_bank_accounts', [
						'headers' => [
							'Content-type' => 'application/json',
							'Authorization' => 'Bearer '.$key,
							'GoCardless-Version' => '2015-07-06'
						],
						'body' => json_encode($body)
					]);
				} catch (\GuzzleHttp\Exception\ClientException $e) {
					 $response = $e->getResponse();
				}
				$contents = $response->getBody()->getContents();
				$rawResponse[] = $contents;
				$responseArr = json_decode($contents,1);
				if($response->getStatusCode() == '201')
				{
					$bankAccount = $responseArr['customer_bank_accounts']['id'];
				}
				else
				{
					print_r($responseArr);
					die('failed to create bank account');
					return ['',$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->first()->id,'gocardless',$this->invoice->total,$this->request->input('paymentMethod.type'),0,$rawResponse,$responseArr['message']];
				}
			}

			//check for mandate for bank account, if not create one
			try {
				$guzzle = new Guzzle();
				$response = $guzzle->request('GET', $url.'/mandates?customer='.$customerId.'&customer_bank_account='.$bankAccount.'&status=active&limit=1', [
					'headers' => [
						'Content-type' => 'application/json',
						'Authorization' => 'Bearer '.$key,
						'GoCardless-Version' => '2015-07-06'
					]
				]);
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				 $response = $e->getResponse();
			}
			$contents = $response->getBody()->getContents();
			$rawResponse[] = $contents;
			$responseArr = json_decode($contents,1);
			if($response->getStatusCode() == '200')
			{
				//found an active mandate now we need to create the charge finally...
				$mandateId = $responseArr['mandates'][0]['id'];
				$chargeDate = $responseArr['mandates'][0]['next_possible_charge_date'];
			}
			else
			{
				//no active mandate on this account, so we gotta create one.

				try {
					$guzzle = new Guzzle();
					$response = $guzzle->request('POST', $url.'/mandates', [
						'headers' => [
							'Content-type' => 'application/json',
							'Authorization' => 'Bearer '.$key,
							'GoCardless-Version' => '2015-07-06'
						],
						'body' => json_encode([
							'mandates'=>[
							'metadata'=>['customerId'=>(string)$this->invoice->customer()->first()->id],
							'links'=>['customer_bank_account'=>$bankAccount],
						]])
					]);
				} catch (\GuzzleHttp\Exception\ClientException $e) {
					 $response = $e->getResponse();
				}
				$contents = $response->getBody()->getContents();
				$rawResponse[] = $contents;
				$responseArr = json_decode($contents,1);
				if($response->getStatusCode() == '201')
				{
					//created mandate
					$mandateId = $responseArr['mandates']['id'];
					$chargeDate = $responseArr['mandates']['next_possible_charge_date'];
				}
				else
				{
					//mandate creation failed for some reason
					print_r($responseArr);
					die('failed to create mandate');
					return [$responseArr['request_id'],$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->first()->id,'gocardless',$this->invoice->total,$this->request->input('paymentMethod.type'),0,$rawResponse,$responseArr['message']];
				}
			}

			$convertedAmount = Controller::convertToCurrency($this->invoice->total,$this->currency,4) * 100;
			try {
				$guzzle = new Guzzle();
				$response = $guzzle->request('POST', $url.'/payments', [
					'headers' => [
						'Content-type' => 'application/json',
						'Authorization' => 'Bearer '.$key,
						'GoCardless-Version' => '2015-07-06'
					],
					'body' => json_encode([
						'payments'=>[
						'amount'=>(string)$convertedAmount,
						'currency'=>'GBP',
						'links'=>['mandate'=>$mandateId],
						'charge_date'=>$chargeDate
					]])
				]);
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				 $response = $e->getResponse();
			}
			$contents = $response->getBody()->getContents();
			$rawResponse[] = $contents;
			$responseArr = json_decode($contents,1);
			if($response->getStatusCode() == '201')
			{
				//charge succesful
				return [$responseArr['payments']['id'],$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->first()->id,'gocardless',$this->invoice->total,$this->request->input('paymentMethod.type'),5,$rawResponse,''];
			}
			else
			{
				//charge failed
				return ['',$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->first()->id,'gocardless',$this->invoice->total,$this->request->input('paymentMethod.type'),0,$rawResponse,$responseArr['message']];
			}
	}

	private function payGatewayPayPalPro()
	{
		$name = substr($this->request->input('billingInfo.firstname'),0,64);
		$name2 = substr($this->request->input('billingInfo.lastname'),0,64);
		$address = substr($this->request->input('billingAddress.address1'),0,64);
		$address2 = substr($this->request->input('billingAddress.address2'),0,64);
		$city = substr($this->request->input('billingAddress.city'),0,32);
		$state = substr($this->request->input('billingAddress.region'),0,32);
		$zip = substr($this->request->input('billingAddress.zip'),0,16);
		$phone = substr($this->request->input('billingInfo.phone'),0,16);
		$email = substr($this->request->input('billingInfo.email'),0,64);
		$country = substr($this->request->input('billingAddress.country'),0,64);
		$ip = $_SERVER['REMOTE_ADDR'];

		$partner = $this->user->getSetting('paypalpro.partner');
		$vendor = $this->user->getSetting('paypalpro.vendor');
		$userPaypalPro = $this->user->getSetting('paypalpro.user');
		$passwordPaypalPro = $this->user->getSetting('paypalpro.password');
		$testmode = $this->user->getSetting('paypalpro.testmode');
		if($testmode)
		{
			$testmode = 'sandbox';
		}
		else
		{
			$testmode = 'live';
		}

		$amount = $this->invoice->total;
		if($this->request->input('paymentMethod.type') == 0)
		{
			//cc
			// VISA, MASTERCARD, AMEX, DISCOVER, MAESTRO, ELO, HIPER, SWITCH, JCB, HIPERCAR
			$cardType = $this->request->input('paymentMethod.cctype');
			$cardNo = $this->request->input('paymentMethod.number');
			$cv2 = $this->request->input('paymentMethod.cvc');
			$ccName = $this->request->input('paymentMethod.cardname');
			/* $ccName = explode(" ",$ccName,2);
			if(!isset($ccName[1]))
			{
					return [0,$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->id,'paypalpro',$amount,$this->request->input('paymentMethod.type'),0,'','Credit Card name must have a first and last name.'];
			} */

			$month = $this->request->input('paymentMethod.expiration.month');
			$year = $this->request->input('paymentMethod.expiration.year');

			if(empty($cardNo))
			{
				abort(500,'Missing Card Number.');
			}
			if(empty($cv2))
			{
				abort(500,'Missing Card CVC.');
			}
			if(empty($month) || empty($year))
			{
				abort(500,'Missing Card Expiration.');
			}

			$saved = 0;
			if(isset($this->paymentMethod['ccsave']) && $this->paymentMethod['ccsave'] == 1)
			{
				try {
					$guzzle = new Guzzle();
					$response = $guzzle->request('POST', 'https://api.sandbox.paypal.com/v2/vault/credit-cards/', [
						'headers' => [
							'Content-type' => 'application/json',
							'Authorization' => 'Bearer '.$key
						],
						'body' => json_encode([
							"number" => $cardNo,
							"type" => $cardType,
							"expire_month" => $month,
							"expire_year" => $year,
							"cvv2" => $cv2,
							"first_name" => $name,
							"last_name" => $name2,
							"billing_address" => [
								"line1" => $address,
								"city" => $city,
								"country_code" => Countries::findOrFail($country)->iso2,
								"postal_code" => $zip,
								"state" => Counties::findOrFail($region)->code,
								"phone" => $phone
							]
						])
					]);
				} catch (\GuzzleHttp\Exception\ClientException $e) {
					 $response = $e->getResponse();
				}
				$contents = $response->getBody()->getContents();
				$rawResponse[] = $contents;
				$responseArr = json_decode($contents,1);

				if(isset($responseArr['valid_until']))
				{
					$default = '';
					if(isset($this->paymentMethod['ccautocharge']) && !empty($this->paymentMethod['ccautocharge']))
					{
						SavedPaymentMethods::where('default','default')->where('user_id',$this->customer->id)->where('type',$this->paymentMethod['type'])->where('gateway_id','paypalpro')->update(['default'=>'']);
						$default = 'default';
					}

					$method = new SavedPaymentMethods();
					$method->type = $this->paymentMethod['type'];
					$method->user_id = $this->customer->id;
					$method->gateway_id = 'paypalpro';
					$method->billing_address_id = $this->billingAddress->id;
					$method->card_type = $cardType;
					$method->last4 = $responseArr['number'];
					$method->expiration_month = $month;
					$method->expiration_year = $year;
					$method->token = $responseArr['id'];
					$method->default = $default;
					$method->save();
					$saved = 1;
				}
			}

			if(isset($this->savedPaymentMethod))
			{
				$creditCardToken = new CreditCardToken();
				$creditCardToken->setCreditCardId($this->savedPaymentMethod['token']);
				$fi = new FundingInstrument();
				$fi->setCreditCardToken($creditCardToken);
			}
			else
			{
				/* $card = new PaymentCard();
				$card->setNumber($cardNo)
					->setType($cardType)
					->setExpireMonth($month)
					->setExpireYear($year)
					->setCvv2($cv2)
					->setFirstName($ccName[0])
					->setLastName($ccName[1]);
				$fi = new FundingInstrument();
				$fi->setCreditCard($card); */

			}

			/* $payerinfo = new PayerInfo();
			$payerinfo->setEmail($this->invoice->customer->email)
				->setFirstName($name)
				->setLastName($name2); */


			/* $payer = new Payer();
			$payer->setPaymentMethod("credit_card")
				->setPayerInfo($payerinfo)
				->setFundingInstruments(array($fi)); */

		}
		elseif($this->request->input('paymentMethod.type') == 1)
		{
			//ach
			$type = $this->request->input('paymentMethod.bank_type');
			$account = $this->request->input('paymentMethod.account');
			$routing = $this->request->input('paymentMethod.routing');

			$iban = strtolower(str_replace(' ','',$account));
			$Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
			$Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);

			if(strlen($iban) == $Countries[substr($iban,0,2)]){

				$MovedChar = substr($iban, 4).substr($iban,0,4);
				$MovedCharArray = str_split($MovedChar);
				$NewString = "";

				foreach($MovedCharArray AS $key => $value){
					if(!is_numeric($MovedCharArray[$key])){
						$MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
					}
					$NewString .= $MovedCharArray[$key];
				}

				if(bcmod($NewString, '97') == 1)
				{
					$atype = 'IBAN';
				}
				else{
					$atype = 'BBAN';
				}
			}
			else{
				$atype = 'BBAN';
			}

			if($type == 0)
			{
				$type = 'CHECKING';
			}
			else
			{
				$type = 'SAVINGS';
			}
			$bank = new BankAccount();
			$bank->setAccountNumber($account)
				->setAccountNumberType($atype)
				->setAccountType($type)
				->setRoutingNumber($routing)
				->setAuthType('web')
				->setFirstName($name)
				->setLastName($name2);

			$fi = new FundingInstrument();
			$fi->setBankAccount($bank);

			/* $payerinfo = new PayerInfo();
			$payerinfo->setEmail($this->invoice->customer->email)
				->setFirstName($name)
				->setLastName($name2); */

			$payer = new Payer();
			$payer->setPaymentMethod("bank")
				->setPayerInfo($payerinfo)
				->setFundingInstruments(array($fi))
				->setExternalRememberMeId($this->invoice->customer->id);
		}

		$newCurrency = Controller::setCurrency();
		$cart = Controller::formatCartData();
		$convertedAmount = $this->invoice->total;
		$convertedTax = $cart['tax'];
		if($newCurrency->id != $this->currency->id)
		{
			$convertedAmount = Controller::convertToCurrency($this->invoice->total,$this->currency->id,$newCurrency) * 100;
			$convertedTax = Controller::convertToCurrency($convertedTax,$this->currency->id,$newCurrency) * 100;
		}

		/* $details = new Details();
		$details->setShipping(0)
			->setTax($convertedTax)
			->setSubtotal($convertedAmount - $convertedTax); */

		/* $amount = new Amount();
		$amount->setCurrency($newCurrency->short_name)
			->setTotal($convertedAmount)
			->setDetails($details); */

		/* $transaction = new Transaction();
		$transaction->setAmount($amount)
			->setDescription($this->user->siteSettings('name') . ' Invoice #'.$this->user->getSetting('invoice.prefix','').$this->invoice->invoice_number); */
		/* $payment = new Payment();
		$payment->setIntent("sale")
			->setPayer($payer)
			->setTransactions(array($transaction)); */

			/*
		$apiContext = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				$clientid,
				$clientsecret
			)
		);

		$apiContext->setConfig(
			array(
				'mode' => 'sandbox',
				'cache.enabled' => false
			)
		); */

		$message = '';
		$status = 0;
		$trans = '';
		//try {
		//	$result = $payment->create($apiContext);
		//} catch (\PayPal\Exception\PayPalConnectionException $e) {
			//$message = json_decode($e->getData(),1)['message'];
			//$status = 0;
		//} catch (Exception $e) {
			//$message = $e->getMessage();
			//$status = 0;
		//}
		//dd($result);

		$payflow = new Payflow;
		$payflow->setEnv($testmode);
		$payflow->setPartner('PayPal');
		$payflow->setVendor($vendor);
		$payflow->setCurrency($newCurrency->short_name);
		$payflow->setUser($userPaypalPro);
		$payflow->setPassword($passwordPaypalPro);
		$payflow->data['ACCT'] = $cardNo;
		$payflow->data['AMT'] = $amount;
		$payflow->data['CVV2'] = $cv2;
		$payflow->data['EXPDATE'] = $month . substr($year, 2);
		$payflow->data['EMAIL'] = $email;
		$payflow->data['FIRSTNAME'] = $name;
		$payflow->data['BILLTOFIRSTNAME'] = $name;
		$payflow->data['SHIPTOFIRSTNAME'] = $name;
		$payflow->data['LASTNAME'] = $name2;
		$payflow->data['BILLTOLASTNAME'] = $name2;
		$payflow->data['SHIPTOLASTNAME'] = $name2;
		$payflow->data['STREET'] = $address;
		$payflow->data['BILLTOSTREET'] = $address;
		$payflow->data['SHIPTOSTREET'] = $address;
		$payflow->data['CITY'] = $city;
		$payflow->data['BILLTOCITY'] = $city;
		$payflow->data['SHIPTOCITY'] = $city;
		$payflow->data['STATE'] = $state;
		$payflow->data['BILLTOSTATE'] = $state;
		$payflow->data['SHIPTOSTATE'] = $state;
		$payflow->data['ZIP'] = $zip;
		$payflow->data['BILLTOZIP'] = $zip;
		$payflow->data['SHIPTOZIP'] = $zip;
		$payflow->data['COUNTRY'] = $country;
		$payflow->data['BILLTOCOUNTRY'] = $country;
		$payflow->data['SHIPTOCOUNTRY'] = $country;
		$result = $payflow->pay();
		if ($result['success'])
		{
			$status = 1;
			$message = 'Payment success'; //$result['message'];
			$trans = $result['data']['PNREF'];
		}
		else
		{
			$message = $result['message'] . '. ' . $result['data']['RESPMSG'];
		}
		if(empty($message))
		{
			$message = '';
		}
		if($this->request->input('paymentMethod.save'))
		{
			//none atm
		}

		session()->put('payment_status', $status);
		session()->put('payment_message', $message);

		//declined or error
		if($status == 0)
		{
				$userFrom = $this->user;
				$subject = $this->user->siteSettings('name') . ' Payment Declined payGatewayPayPalPro';
				$content = '--content payment declined--';
				$view = 'Checkout.mail.paymentErrorEmail';
				Mail::to($this->invoice->customer)->send(new GeneralEmail($userFrom, $subject, $content, $view));
		}

		return [$trans,$this->invoice->id,$this->invoice->user->id,$this->invoice->customer->id,'paypalpro',$convertedAmount,$this->paymentMethod['type'],$status,'',$message, $result];
	}

	private function payGatewayStripe()
	{
		Stripe::setApiKey($this->user->getSetting('stripe.secretkey'));

		$formCard = $this->request->get('paymentMethod');
		$tokken = \Stripe\Token::create([
            'card' => [
                'name' => $formCard['cardname'],
                'number' => $formCard['number'],
                'exp_month' => $formCard['expiration']['month'],
                'exp_year' => $formCard['expiration']['year'],
                'cvc' => $formCard['cvc']
            ],
        ]);

		if (!is_object($this->request)) {
			abort(500, 'Invalid request object.');
		}

		/**
		 * Get stripe save
		 */
		$savedCustomerId = $this->customer->getSetting('stripe.id');

		if (isset($this->savedPaymentMethod)) {
			$source = $this->savedPaymentMethod->token;
		} else {
			try {
				$cardInfo = \Stripe\Source::create([
					"type" => "card",
					"token" => $tokken['id'],
					"owner" => [
						"email" => $this->billingAddress->email
					],
				]);

				$source = $cardInfo->id;
			} catch(Exception $e) {
				// error
			}
		}

		$amount = $this->invoice->total;

		if ($this->invoice->currency_id == 0) {

			/**
			 * In case Currency is not set, default to GBP
			 */
			$currency = 'gbp';
		} else {
			$currency = strtolower(Currency::where('id', $this->invoice->currency_id)->first()->short_name);
		}

		if (!empty($this->request->input('chosenCC'))) {
			try {
				$charge = \Stripe\Charge::create(array(
					"amount" => $amount*100,
					"currency" => $currency,
					"source" => $source,
					"customer" => $savedCustomerId)
				);
			} catch(\Stripe\Error\Card $e) {
				abort (500, 'Card was declined');
			}
		} else {
			if (isset($this->paymentMethod['ccsave']) && $this->paymentMethod['ccsave'] == 1) {
				$stripeCustomer = '';
				if (!empty($savedCustomerId)) {
					$stripeCustomer = \Stripe\Customer::retrieve($savedCustomerId);
					$stripeCustomer->source = $source;
					$stripeCustomer->save();
				}
				if(empty($stripeCustomer)) {
					$firstname = substr($this->billingAddress->firstname,0,32);
					$lastname = substr($this->billingAddress->lastname,0,32);

					$stripeCustomer = \Stripe\Customer::create(array(
						"source" => $source,
						"description" => $firstname . " " . $lastname)
					);

					$settings = new User_Setting();
					$settings->user_id = $this->invoice->customer->id;
					$settings->name = 'stripe.id';
					$settings->value = $stripeCustomer->id;
					$settings->save();
				}

				try {
					$charge = \Stripe\Charge::create(array(
						"amount" => $amount*100,
						"currency" => $currency,
						"customer" => $stripeCustomer->id,
						"source" => $source)
					);
				} catch(\Stripe\Error\Card $e) {
					abort (500, 'Card was declined');
				}

				$default = '';
				if(isset($this->paymentMethod['ccautocharge']) && !empty($this->paymentMethod['ccautocharge']))
				{
					SavedPaymentMethods::where('default','default')->where('user_id',$this->customer->id)->where('type',$this->paymentMethod['type'])->where('gateway_id','stripe')->update(['default'=>'']);
					$default = 'default';
				}

				$method = new SavedPaymentMethods();
				$method->type = $this->paymentMethod['type'];
				$method->user_id = $this->customer->id;
				$method->gateway_id = 'stripe';
				$method->billing_address_id = $this->billingAddress->id;
				$method->card_type = $cardInfo->card->brand;
				$method->last4 = $cardInfo->card->last4;
				$method->expiration_month = $cardInfo->card->exp_month;
				$method->expiration_year = $cardInfo->card->exp_year;
				$method->token = $source;
				$method->default = $default;
				$method->save();

			} else {
				try {
					$charge = \Stripe\Charge::create(array(
						"amount" => $amount*100,
						"currency" => $currency,
						"source" => $source
					));
				} catch(\Stripe\Error\Card $e) {
					abort (500, 'Card was declined');
				}
			}
		}

		if ($charge->status == "succeeded") {
			$status = 1;
		} else {
			$status = 0;
		}

		return [
			$charge->id,
			$this->invoice->id,
			$this->invoice->user->id,
			$this->invoice->customer->id,
			'stripe',$amount,
			$this->paymentMethod['type'],
			$status,$charge,
			""
		];
	}

	private function payGatewaycardinity()
	{
        $client = CardinityClient::create([
            'consumerKey' => $this->user->getSetting('cardinity.consumer_key'),
            'consumerSecret' => $this->user->getSetting('cardinity.consumer_secret')
        ]);

        $amount = $this->invoice->total;
        $invoiceId = $this->invoice->id;

        $country = Countries::find($this->request->input('billingAddress.country'));

        $method = new CardinityPayment\Create([
            'amount' => (float) $amount,
            'currency' => $this->user->getSetting('cardinity.currency'),
            'settle' => true,
            'description' => $this->package ? $this->package->name : '',
            'order_id' => strval($invoiceId),
            'country' => $country->iso2,
            'payment_method' => CardinityPayment\Create::CARD,
            'payment_instrument' => [
                'pan' => $this->paymentMethod['number'],
                'exp_year' => intval($this->paymentMethod['expiration']['year']),
                'exp_month' => intval($this->paymentMethod['expiration']['month']),
                'cvc' => $this->paymentMethod['cvc'],
                'holder' => $this->paymentMethod['cardname']
            ],
				    'threeds2_data' =>  [
				        "notification_url" => 'https://'.Config('app.site')->domain.'/checkout/callback',
				        "browser_info" => [
				            "accept_header" => "text/html",
				            "browser_language" => "en-US",
				            "screen_width" => 600,
				            "screen_height" => 400,
				            'challenge_window_size' => "600x400",
				            "user_agent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0",
				            "color_depth" => 24,
				            "time_zone" => -60
				        ],
				    ],
        ]);

        /**
         * In case payment could not be processed exception will be thrown.
         * In this example only Declined and ValidationFailed exceptions are handled. However there is more of them.
         * See Error Codes section for detailed list.
         */

        $errors = [];
        $paymentId = null;
        $serialize = null;
        $status = null;
				$auth = null;

        try {
            $payment = $client->call($method);
            $status = $payment->getStatus();
            $paymentId = $payment->getId();
            $serialize = $payment->serialize();

            if ($status === 'pending') {
								if ($payment->isThreedsV2())
								{
										// $auth object for data required to finalize payment
										$auth = $payment->getThreeds2Data();
										session()->put('cardinity.auth', $auth);
										// finalize process should be done here.
								}
								session()->put('cardinity.paymentId', $paymentId);
								session()->put('cardinity.invoiceId', $invoiceId);
            }
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
            $errors = [
                $exception->getMessage()
            ];
        }

        if (!empty($errors)) {
            session()->put('cardinity_errors', $errors);
        }

        return [
            $paymentId,
            $invoiceId,
            $this->invoice->user->id,
            $this->invoice->customer->id,
            'cardinity',
            $amount,
            $this->paymentMethod['type'],
            $status,
            $serialize,
            '',
						$auth
        ];
	}

	private function payGatewaybanktransfer()
	{
			$paymentId = null;
			$serialize = null;
			$status = null;
			$auth = null;
			$invoiceId = $this->invoice->id;
			$amount = $this->invoice->total;

			return [
					$paymentId,
					$invoiceId,
					$this->invoice->user->id,
					$this->invoice->customer->id,
					'bank_transfer',
					$amount,
					$this->paymentMethod['type'],
					$status,
					$serialize,
					'',
					$auth
			];
	}

	public function getUsersSavedPaymentMethodByType($type, $customer_id = null)
	{
		$gateway = Controller::site('defaultGateway');

		if($type == 2) //stripe
		{
				Stripe::setApiKey($this->user->getSetting('stripe.secretkey'));

				$method = PaymentMethod::all([
				'customer' => $customer_id,
				'type' => 'card',
				]);

				return $method;
		}
		$method = SavedPaymentMethods::where('user_id',$this->customer->id)->where('gateway_id',$gateway)->where('type',$type)->where('default','default')->first();
		return $method;
	}

	public function getUsersSavedPaymentMethod($id)
	{
		$gateway = Controller::site('defaultGateway');
		$method = SavedPaymentMethods::where('user_id',$this->customer->id)->where('gateway_id',$gateway)->where('id',$id)->first();
		return $method;
	}

	public function getUsersSavedPaymentMethods()
	{
		$gateway = Controller::site('defaultGateway');

		if (empty($gateway)) {
			$gateway = '';
		}

		$arrMethods = [];
		$saveMethodsEnabled = 0;

		if (array_key_exists($gateway,$this->savePaymentMethodGateways)) {
			$saveMethodsEnabled = 1;

			if (!empty($this->customer)) {
				$arrMethods = SavedPaymentMethods::where('user_id',$this->customer->id)->where('gateway_id',$gateway)->get();
			}
		}

		foreach ($arrMethods as $k=>$v) {
			if ($v->type == 0) {
				switch($gateway)
				{
					case 'bluepay':
						$arrMethods[$k]->last4 = $v->last4;
						$arrMethods[$k]->exp_month = $v->expiration_month;
						$arrMethods[$k]->exp_year = $v->expiration_year;
					break;
					case 'stripe':
						$arrMethods[$k]->last4 = $v->last4;
						$arrMethods[$k]->exp_month = $v->expiration_month;
						$arrMethods[$k]->exp_year = $v->expiration_year;
					break;
					case 'paypalpro':
						$temp = getTokenDataPayPalPro($v->token);
						if($temp)
						{
							$arrMethods[$k]->last4 = $temp->last4;
							$arrMethods[$k]->exp_month = $temp->expiration_month;
							$arrMethods[$k]->exp_year = $temp->expiration_year;
						}
					break;
				}
			}
			elseif($v->type == 1)
			{
				switch($gateway)
				{
					case 'bluepay':
						$arrMethods[$k]->last4 = $v->last4;
					break;
					case 'stripe':
						$arrMethods[$k]->last4 = $temp->last4;
					break;
				}
			}
		}
		return [$arrMethods,$saveMethodsEnabled];
	}

	private function getTokenDataPayPalPro($token)
	{

		$clientid = $this->user->getSetting('paypalpro.clientid');
		$clientsecret = $this->user->getSetting('paypalpro.clientsecret');
		$testmode = $this->user->getSetting('paypalpro.testmode');
		if($testmode)
		{
			$testmode = 'sandbox';
		}
		else
		{
			$testmode = 'live';
		}

		try {
			$guzzle = new Guzzle();
			$response = $guzzle->request('GET', 'https://api.sandbox.paypal.com/v2/vault/credit-cards/'.$token, [
				'headers' => [
					'Content-type' => 'application/json',
					'Authorization' => 'Bearer '.$clientsecret
				]
			]);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			 $response = $e->getResponse();
		}
		$contents = $response->getBody()->getContents();
		$rawResponse[] = $contents;
		$responseArr = json_decode($contents,1);
		if(isset($responseArr['valid_until']))
		{
			$now = new DateTime("now");
			$datetime = DateTime::createFromFormat(DateTime::ISO8601, $responseArr['valid_until']);
			if($datetime > $now)
			{
				return ['last4'=>$responseArr['number'],'exp_month'=>$responseArr['expire_month'],'exp_year'=>$responseArr['expire_year']];
			}
		}
		return false;
	}
}
