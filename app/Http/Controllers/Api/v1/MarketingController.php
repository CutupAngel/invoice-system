<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Address;
use App\Currency;
use App\Counties;
use App\Countries;
use App\Discount;
use App\Integration;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceTotal;
use App\Login_History;
use App\MiscStorage;
use App\Order;
use App\Order_Options;
use App\Package;
use App\Package_Cycle;
use App\Package_Option_Values;
use App\User;
use App\User_Contact;
use App\User_Link;
use App\User_Setting;
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

class MarketingController extends Controller
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

		/* Create Discount
			 Params
			 Header:
				- token: string (required)

				Body:
				- minimum_value: double (required)
				- discount_value: double (required)
				- start: date (yyyy-mm-dd) (required)
				- end: date (yyyy-mm-dd) (blank if 'indefinite') (required)
				- type: integer (0 = percentage | 1 = fixed) (required)
		*/
		public function createDiscount(Request $request)
		{
				if(trim($request->minimum_value) == '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Minimum value is required.'
																	],
																	401);
				}

				if($request->type == Discount::CODE)
				{
						if($request->input('discount_value') > 100 || $request->input('discount_value') < 0)
						{
								return Response::json([
																					'success' => false,
																					'errors' => trans('backend.inv-totalingorder-discountpercentage-error')
																			],
																			401);
						}
				}
				else
				{
						if($request->input('discount_value') < 0 || $request->input('discount_value') > $request->minimum_value)
						{
								return Response::json([
																					'success' => false,
																					'errors' => trans('backend.inv-totalingorder-discountfixed-error')
																			],
																			401);
						}
				}

				$startDate = explode('-', $request->start);
				if($startDate[0] > 9999)
				{
						return Response::json([
																			'success' => false,
																			'errors' => trans('backend.marketing-discount-start-date-error')
																	],
																	401);
				}

				$endDate = explode('-', $request->end);
				if($endDate[0] > 9999)
				{
						return Response::json([
																			'success' => false,
																			'errors' => trans('backend.marketing-discount-end-date-error')
																	],
																	401);
				}

				$discount = new Discount;
				$discount->user_id = $this->user->id;
				$discount->type = strip_tags($request->type);
				$value = strip_tags($request->minimum_value);
				$discount->value = $value;
				$discount->discount = strip_tags($request->input('discount_value'));
				$discount->start = strip_tags($request->input('start'));
				$discount->end = strip_tags((($request->input('end') == '') ? '0000-00-00' : $request->input('end')));

				$discount->api_type = $this->api_type;
				$discount->save();

				return Response::json([
						'success' => true,
						'discount_id' => $discount->id,
						'message' => 'Discount created successfully',
				], 200);
		}

		/* Update Discount
			 Params
			 Header:
				- token: string (required)

				Body:
				- discount_id: integer (required)
				- minimum_value: double (required)
				- discount_value: double (required)
				- start: date (yyyy-mm-dd) (required)
				- end: date (yyyy-mm-dd) (blank if 'indefinite') (required)
				- type: integer (0 = percentage | 1 = fixed) (required)
		*/
		public function updateDiscount(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('discount_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Discount ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				if(trim($request->minimum_value) == '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Minimum value is required.'
																	],
																	401);
				}

				if($request->type == Discount::CODE)
				{
						if($request->input('discount_value') > 100 || $request->input('discount_value') < 0)
						{
								return Response::json([
																					'success' => false,
																					'errors' => trans('backend.inv-totalingorder-discountpercentage-error')
																			],
																			401);
						}
				}
				else
				{
						if($request->input('discount_value') < 0 || $request->input('discount_value') > $request->minimum_value)
						{
								return Response::json([
																					'success' => false,
																					'errors' => trans('backend.inv-totalingorder-discountfixed-error')
																			],
																			401);
						}
				}

				$startDate = explode('-', $request->start);
				if($startDate[0] > 9999)
				{
						return Response::json([
																			'success' => false,
																			'errors' => trans('backend.marketing-discount-start-date-error')
																	],
																	401);
				}

				$endDate = explode('-', $request->end);
				if($endDate[0] > 9999)
				{
						return Response::json([
																			'success' => false,
																			'errors' => trans('backend.marketing-discount-end-date-error')
																	],
																	401);
				}

				$discount = Discount::where('id', $request->discount_id)
															->where('api_type', $this->api_type)
															->first();

				if($this->api_type == 'sandbox')
					$discount = Discount::where('id', $request->discount_id)
																->where('api_type', $this->api_type)
																->first();
				else
					$discount = Discount::where('id', $request->discount_id)
																->where(function($query) {
																		$query->whereNull('api_type')
																					->orWhere('api_type', $this->api_type);
																})
																->first();

				if(!$discount)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Discount ID: ' . $request->discount_id . ' not found.'
																	],
																	401);
				}

				$discount->user_id = $this->user->id;
				$discount->type = strip_tags($request->type);
				$value = strip_tags($request->minimum_value);
				$discount->value = $value;
				$discount->discount = strip_tags($request->input('discount_value'));
				$discount->start = strip_tags($request->input('start'));
				$discount->end = strip_tags((($request->input('end') == '') ? '0000-00-00' : $request->input('end')));

				$discount->api_type = $this->api_type;
				$discount->save();

				return Response::json([
						'success' => true,
						'discount_id' => $discount->id,
						'message' => 'Discount updated successfully',
				], 200);
		}

		/* Delete Discount
			 Params
			 Header:
				- token: string (required)

				Body:
				- discount_id: integer (required)
		*/
		public function deleteDiscount(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('discount_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Discount ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				if($this->api_type == 'sandbox')
					$discount = Discount::where('id', $request->discount_id)
																->where('api_type', $this->api_type)
																->first();
				else
					$discount = Discount::where('id', $request->discount_id)
																->where(function($query) {
																		$query->whereNull('api_type')
																					->orWhere('api_type', $this->api_type);
																})
																->first();

				if(!$discount)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Discount ID: ' . $request->discount_id . ' not found.'
																	],
																	401);
				}

				$discount->delete();

				return Response::json([
						'success' => true,
						'message' => 'Discount ID: ' . $request->discount_id . ' deleted successfully',
				], 200);
		}

		/* Get Discount by ID
			 Params
			 Header:
				- token: string (required)

				Body:
				- discount_id: integer (required)
		*/
		public function getDiscountById(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('discount_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Discount ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				if($this->api_type == 'sandbox')
					$discount = Discount::where('id', $request->discount_id)
																->where('api_type', $this->api_type)
																->first();
				else
					$discount = Discount::where('id', $request->discount_id)
																->where(function($query) {
																		$query->whereNull('api_type')
																					->orWhere('api_type', $this->api_type);
																})
																->first();

				if(!$discount)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Discount ID: ' . $request->discount_id . ' not found.'
																	],
																	401);
				}

				return Response::json([
						'success' => true,
						'discount' => $discount,
				], 200);
		}

		/* Get Lists Discount
			 Params
			 Header:
				- token: string (required)

				Body:
				- type: integer (0 = FIXED | 1 = CODE | {empty} = ALL) (optional)
		*/
		public function listsDiscount(Request $request)
		{
				$errorMessage = '';

				$type = '';
				if($request->type) $type = $request->type;

				if($this->api_type == 'sandbox')
				{
					$discount = Discount::where('api_type', $this->api_type);
				}
				else
				{
					$discount = Discount::where(function($query) {
																		$query->whereNull('api_type')
																					->orWhere('api_type', $this->api_type);
																});
				}

				if($type != '')
				{
					$discount = $discount->where('type', $type);
				}

				$discount = $discount->orderBy('id')->get();

				return Response::json([
						'success' => true,
						'discounts' => $discount,
				], 200);
		}

}
