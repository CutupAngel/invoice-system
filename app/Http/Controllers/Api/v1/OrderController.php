<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Address;
use App\Currency;
use App\Counties;
use App\Countries;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceTotal;
use App\MiscStorage;
use App\Order;
use App\Order_Options;
use App\Package;
use App\Package_Cycle;
use App\Package_Option_Values;
use App\User;
use App\User_Contact;
use App\User_Link;
use App\Transactions;
use App\Http\Controllers\PaymentController;
use App\Mail\GeneralEmail;
use App\Mail\InvoiceEmail;
use Auth;
use DB;
use DateTime;
use Hash;
use Invoices;
use Permissions;
use Mail;
use Settings;
use Response;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use FraudLabsPro\Configuration as FraudConfiguration;
use FraudLabsPro\Order as FraudOrder;

class OrderController extends Controller
{
		public $user = null;
		public $api_type = null;

		public function __construct(Request $request)
		{
				$user = User::where('sandbox_api_key', $request->header('token'))
											->orWhere('live_api_key', $request->header('token'))
											->first();

				if(!$user)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Invalid token.'
																	],
																	401);
				}

				$this->user = $user;

				if($user->sandbox_api_key == $request->header('token')) $this->api_type = 'sandbox';
				else if($user->live_api_key == $request->header('token')) $this->api_type = 'live';
		}

		/* Accept Order
			 Params
			 Header:
				- token: string (required)

				Body:
				- order_id: integer (required)
		*/
		public function acceptOrder(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('order_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Order ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$order_id = $request->order_id;

				if($this->api_type == 'sandbox')
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$order)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Order ID: ' . $order_id . ' not found.'
																	],
																	400);
				}

				$order->status = Order::SETUP;
				$order->save();

				return Response::json([
						'success' => true,
						'message' => 'Order ID: ' . $order->id . ' accepted successfully'
				], 200);
		}

		/* Add Order
			 Params
			 Header:
				- token: string (required)

				Body:
				- customer_id: integer (required)
				- package_id[]: integer (required)
				- cycle_id[]: integer (required)
				- options[id][]: integer (optional)
				- options[amount][]: double (optional)
				- options[value][]: string (optional)
				- options[cycle_type][]: integer (optional)
		*/

		public function AddOrder(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('customer_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is required.';
				}

				if($request->customer_id && !is_numeric($request->customer_id))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID format is invalid.';
				}

				if(!$request->has('package_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Package ID is required.';
				}

				if(!$request->has('cycle_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Cycle ID is required.';
				}

				if(!$request->has('price'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Price is required.';
				}

				if($request->has('price') && !is_double(floatval($request->has('price'))))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Price is not valid format.';
				}

				$cycle_id = $request->cycle_id;

				foreach($cycle_id as $id)
				{
						$packageCycle = Package_Cycle::find($id);
						if(!$packageCycle)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Cycle ID is not found.';
								break;
						}
				}

				$customer_id = $request->customer_id;
				$customer = User::find($customer_id);
				if(!$customer)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer not found.';
				}

				if ($request->has('options'))
				{
						$countRecordOptions = count($request->options['id']);

						for($x = 0 ; $x < $countRecordOptions ; $x++)
						{
								$optionValue = Package_Option_Values::find($request->options['id'][$x]);
								if(!$optionValue)
								{
										if($errorMessage != '') $errorMessage .= ', ';
										$errorMessage .= 'Option ID is not found.';
										break;
								}
						}
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$price = $request->price;
				$package_id = $request->package_id;

				$subTotal = 0;
				$x = 0;
				foreach ($package_id as $id)
				{
						$package = Package::findOrFail($id);
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
						$order->user_id = $this->user->id;
						$order->customer_id = $customer_id;
						$order->package_id = $package->id;
						$order->cycle_id = $cycle_id[$x];
						$order->last_invoice = date('Y-m-d H:i:s');
						$order->price = $price;
						$order->currency_id = $this->user->getSetting('site.defaultCurrency',4);
						$order->integration = '';
						$order->trial_order = $trialOrder;
						$order->trial_expire_date = $trialExpireDate;
						$order->trial_expire_time = $trialExpireTime;
						$order->domainIntegration = 0;
						$order->api_type = $this->api_type;

						if (!empty($package->integration)) {
							$order->integration = $package->integration;
						}

						if (!empty($package->domainIntegration)) {
							$order->domainIntegration = $package->domainIntegration;
						}

						$order->save();

						/*
						* Set invoice number with check max
						*/
						$next = DB::table('invoices')->where('user_id', Controller::site('id'))->max('invoice_number') + 1;
						if($next < Settings::get('invoice.startNumber', 0)) {
							$next = Settings::get('invoice.startNumber', 0);
						}

						$invoice = new Invoice();
						$invoice->user_id = $this->user->id;
						$invoice->customer_id = $customer_id;
						$invoice->order_id = $order->id;
						$invoice->address_id = $customer->mailingContact->address->id;
						$invoice->total = $price;
						$invoice->status = 0;
						$invoice->credit = 0.00;
						$invoice->invoice_number = $next;
						$invoice->due_at = date('Y-m-d');
						$invoice->currency_id = $this->user->getSetting('site.defaultCurrency',4);
						$invoice->api_type = $this->api_type;
						$invoice->save();

						$invoice = Invoice::find($invoice->id);
						if (!empty($order->integration) && $invoice->status == 1) {
							Integrations::get($order->integration, 'completeOrder', [$order]);
						}

						if (!empty($order->domainIntegration) && $invoice->status == 1) {
							Integrations::get('domain', 'completeOrder', [$order]);
						}
						$x++;
				}


				if ($request->has('options'))
				{
						$countRecordOptions = count($request->options['id']);

						for($x = 0 ; $x < $countRecordOptions ; $x++)
						{
								Order_Options::create([
									'order_id' => $order->id,
									'option_value_id' => $request->options['id'][$x],
									'amount' => $request->options['amount'][$x],
									'value' => $request->options['value'][$x],
									'cycle_type' => $request->options['cycle_type'][$x],
									'status' => Order_Options::SETUP,
									'last_invoice' => date('Y-m-d H:i:s'),
								]);
						}
				}

				return Response::json([
						'success' => true,
						'message' => 'Order created successfully',
						'order_id' => $order->id
				], 200);
		}

		/* Cancel Order
			 Params
			 Header:
				- token: string (required)

				Body:
				- order_id: integer (required)
		*/
		public function cancelOrder(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('order_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Order ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$order_id = $request->order_id;

				if($this->api_type == 'sandbox')
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$order)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Order ID: ' . $order_id . ' not found.'
																	],
																	400);
				}

				$order->status = Order::CANCELLED;
				$order->save();

				return Response::json([
						'success' => true,
						'message' => 'Order ID: ' . $order->id . ' cancelled successfully'
				], 200);
		}

		/* Delete Order
			 Params
			 Header:
				- token: string (required)

				Body:
				- order_id: integer (required)
		*/
		public function deleteOrder(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('order_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Order ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$order_id = $request->order_id;

				if($this->api_type == 'sandbox')
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$order)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Order ID: ' . $order_id . ' not found.'
																	],
																	400);
				}

				$order->delete();

				return Response::json([
						'success' => true,
						'message' => 'Order ID: ' . $order->id . ' deleted successfully'
				], 200);
		}

		/* Fraud Order
			 Params
			 Header:
				- token: string (required)

				Body:
				- order_id: integer (required)
				- fraundlabs_json: string (json) (optional)
		*/
		public function fraudOrder(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('order_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Order ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$order_id = $request->order_id;

				if($this->api_type == 'sandbox')
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$order)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Order ID: ' . $order_id . ' not found.'
																	],
																	400);
				}

				$customer = User::find($order->customer_id);
				$customer->fraudlabs_status = 'REJECT';

				$fraudlabsJson = '';
				if($request->fraudlabs_json) $fraudlabsJson = $request->fraudlabs_json;
				$customer->fraudlabs_json = $fraudlabsJson;
				$customer->save();

				return Response::json([
						'success' => true,
						'message' => 'Order ID: ' . $order->id . ' set fraud successfully'
				], 200);
		}

		/* Get Orders
			 Params
			 Header:
				- token: string (required)
		*/
		public function getOrders(Request $request)
		{
				if($this->api_type == 'sandbox')
					$orders = Order::where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->orderBy('id')
															->get();
				else
					$orders = Order::where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->orderBy('id')
															->get();

				$orderArr = [];
				foreach($orders as $order)
				{
						$orderObj = new \stdClass();
						$orderObj->id = $order->id;
						$orderObj->user = $order->user;
						$orderObj->customer = $order->customer;
						$orderObj->package = $order->package;
						$orderObj->cycle = $order->cycle;
						$orderObj->status = $order->status;
						$orderObj->last_invoice = $order->last_invoice;
						$orderObj->price = $order->price;
						$orderObj->currency = $order->currency;
						$orderObj->integration = $order->integration;
						$orderObj->trial_order = $order->trial_order;
						$orderObj->trial_expire_date = $order->trial_expire_date;
						$orderObj->trial_expire_time = $order->trial_expire_time;
						$orderObj->fraudlabs_status = $order->fraudlabs_status;
						$orderObj->fraudlabs_json = $order->fraudlabs_json;
						$orderObj->api_type = $order->api_type;
						$orderObj->created_at = $order->created_at;
						$orderObj->updated_at = $order->updated_at;
						$orderObj->deleted_at = $order->deleted_at;
						$orderObj->domainIntegration = $order->domainIntegration;
						$orderArr[] = $orderObj;
				}

				return Response::json([
						'success' => true,
						'orders' => $orderArr
				], 200);
		}


		/* Get Order By Status
			 Params
			 Header:
				- token: string (required)

				Body:
				- status: string (required) (RECENT | PENDING | SETUP | SHIPPED | CANCELLED | RETURNED | TERMINATED | SUSPENDED | ERROR | ALL)
		*/
		public function getOrdersByStatus(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('status'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Status is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$status = $request->status;
				if($status == 'RECENT' || $status == 'PENDING' || $status == 'SETUP' || $status == 'SHIPPED' || $status == 'CANCELLED' || $status == 'RETURNED' || $status == 'TERMINATED' || $status == 'SUSPENDED' || $status == 'ERROR' || $status == 'ALL')
				{
						if($this->api_type == 'sandbox')
						{
								$orders = Order::where('user_id', $this->user->id)
																		->where('api_type', $this->api_type);

								if($status <> 'ALL')
								{
										$orders = $orders->where('status', constant('App\Order::' . strtoupper($status)));
								}
								$orders = $orders->orderBy('id')->get();
						}
						else
						{
								$orders = Order::where('user_id', $this->user->id)
																		->where(function($query) {
																				$query->whereNull('api_type')
																							->orWhere('api_type', $this->api_type);
																		});

								if($status <> 'ALL')
								{
										$orders = $orders->where('status', constant('App\Order::' . strtoupper($status)));
								}
								$orders = $orders->orderBy('id')->get();
						}

						$orderArr = [];
						foreach($orders as $order)
						{
								$orderObj = new \stdClass();
								$orderObj->id = $order->id;
								$orderObj->user = $order->user;
								$orderObj->customer = $order->customer;
								$orderObj->package = $order->package;
								$orderObj->cycle = $order->cycle;
								$orderObj->status = $order->status;
								$orderObj->last_invoice = $order->last_invoice;
								$orderObj->price = $order->price;
								$orderObj->currency = $order->currency;
								$orderObj->integration = $order->integration;
								$orderObj->trial_order = $order->trial_order;
								$orderObj->trial_expire_date = $order->trial_expire_date;
								$orderObj->trial_expire_time = $order->trial_expire_time;
								$orderObj->fraudlabs_status = $order->fraudlabs_status;
								$orderObj->fraudlabs_json = $order->fraudlabs_json;
								$orderObj->api_type = $order->api_type;
								$orderObj->created_at = $order->created_at;
								$orderObj->updated_at = $order->updated_at;
								$orderObj->deleted_at = $order->deleted_at;
								$orderObj->domainIntegration = $order->domainIntegration;
								$orderArr[] = $orderObj;
						}

						return Response::json([
								'success' => true,
								'orders' => $orderArr
						], 200);
				}
				else
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Status is not valid.'
																	],
																	400);
				}
		}

		/* Get Order By Status
			 Params
			 Header:
				- token: string (required)

				Body:
				- order_id: integer (required)
		*/
		public function checkFraudOrder(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('order_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Order ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$order_id = $request->order_id;

				if($this->api_type == 'sandbox')
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$order)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Order ID: ' . $order_id . ' not found.'
																	],
																	400);
				}

				$orderDetails = [
														'order' => [
																					//'orderId'		=> $order->id,
																					'note'			=> 'Bserv',
																					'currency'		=> $order->currency->short_name,
																					'amount'		=> $order->price,
																					'quantity'		=> 1,

																					// Please refer reference section for full list of payment methods
																					'paymentMethod'	=> FraudOrder::CREDIT_CARD,
														],

														'card'		=> [
															'number'	=> $request->has('paymentMethod.number') ? $request->input('paymentMethod.number') : '',
														],

														'billing'	=> [
															'firstName'	=> $order->customer->mailingContact->getFirstName,
															'lastName'	=> $order->customer->mailingContact->getLastName,
															'email'		=> $order->customer->mailingContact->email,
															'phone'		=> $order->customer->mailingContact->phone,

															'address'	=> $order->customer->mailingContact->address_1,
															'city'		=> $order->customer->mailingContact->city,
															'state'		=> @$order->customer->mailingContact->county->code,
															'postcode'	=> $order->customer->mailingContact->postal_code,
															'country'	=> @$order->customer->mailingContact->country->iso2,
														],
				];

				// Sends the order details to FraudLabs Pro

				$result = FraudOrder::validate($orderDetails);

				return Response::json([
						'success' => true,
						'fraud_result' => $result
				], 200);
		}

		/* Pending Order
			 Params
			 Header:
				- token: string (required)

				Body:
				- order_id: integer (required)
		*/
		public function pendingOrder(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('order_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Order ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$order_id = $request->order_id;

				if($this->api_type == 'sandbox')
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$order = Order::where('id', $order_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$order)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Order ID: ' . $order_id . ' not found.'
																	],
																	400);
				}

				$order->status = Order::PENDING;
				$order->save();

				return Response::json([
						'success' => true,
						'message' => 'Order ID: ' . $order->id . ' set pending successfully'
				], 200);
		}
}
