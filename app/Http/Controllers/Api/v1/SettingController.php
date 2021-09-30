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
use App\TaxZones;
use App\TaxZoneCounties;
use App\TaxClasses;
use App\TaxRates;
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

class SettingController extends Controller
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

		/* Create Staff
			 Params
			 Header:
				- token: string (required)

				Body:
				- name: string (required)
				- email: string (email) (required)
				- username: string (required)
				- password: string (optional)
				- permission[dashboard]: string ('Y') (optional)
				- permission[packages]: string ('Y') (optional)
				- permission[customers]: string ('Y') (optional)
				- permission[invoices]: string ('Y') (optional)
				- permission[marketing]: string ('Y') (optional)
				- permission[reports]: string ('Y') (optional)
				- permission[support]: string ('Y') (optional)
				- permission[settings]: string ('Y') (optional)
		*/
		public function createStaff(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}

				if(!$request->has('email'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Email is required.';
				}

				if(!$request->has('username'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Username is required.';
				}

				if($request->email)
				{
						if(!filter_var($request->email, FILTER_VALIDATE_EMAIL))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Email is not valid format.';
						}
				}

				if($request->username)
				{
						if(!filter_var($request->username, FILTER_VALIDATE_EMAIL))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Username is not valid format.';
						}
						$userExist = User::where('username', $request->username)->first();
						if($userExist)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Username is already exists. Please choose another.';
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

				// Generate the password if the user didn't supply one.
				if ($request->has('password')) {
					$password = $request->password;
				} else {
					$password = str_random(12);
				}

				$user = new User();
				$user->name = $request->name;
				$user->username = $request->username;
				$user->email = $request->email;
				$user->password = bcrypt($password);
				$user->account_type = 3;
				$user->api_type = $this->api_type;
				$user->save();

				$userLink = new User_Link();
				$userLink->parent_id = $this->user->id;
				$userLink->user_id = $user->id;
				$userLink->save();

				if ($request->has('permission')) {
					foreach ($request->input('permission') as $permission => $value) {
						$settings = new User_Setting();
						$settings->user_id = $user->id;
						$settings->name = "permission." . $permission;
						$settings->value = $value;
						$settings->save();
					}
				}

				$currentUser = $this->user;
				Mail::send('Settings.staffEmail', [
					'name' => $user->name,
					'username' => $user->username,
					'password' => $password,
					'siteURL' => $user->siteSettings('url'),
					'siteName' => $user->siteSettings('name')
				], function ($m) use ($user, $currentUser) {
					$m->from($currentUser->email, $currentUser->name);
					$m->to($user->email, $user->name);
					$m->subject('BillingServ Account Details');
				});

				return Response::json([
						'success' => true,
						'user_id' => $user->id,
						'message' => 'Staff created successfully',
				], 200);
		}

		/* Update Staff
			 Params
			 Header:
				- token: string (required)

				Body:
				- user_id: integer (required)
				- name: string (required)
				- email: string (email) (required)
				- username: string (required)
				- password: string (optional)
				- permission[dashboard]: string ('Y') (optional)
				- permission[packages]: string ('Y') (optional)
				- permission[customers]: string ('Y') (optional)
				- permission[invoices]: string ('Y') (optional)
				- permission[marketing]: string ('Y') (optional)
				- permission[reports]: string ('Y') (optional)
				- permission[support]: string ('Y') (optional)
				- permission[settings]: string ('Y') (optional)
		*/
		public function updateStaff(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('user_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'User ID is required.';
				}

				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}

				if(!$request->has('email'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Email is required.';
				}

				if(!$request->has('username'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Username is required.';
				}

				if($request->email)
				{
						if(!filter_var($request->email, FILTER_VALIDATE_EMAIL))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Email is not valid format.';
						}
				}

				if($request->username)
				{
						if(!filter_var($request->username, FILTER_VALIDATE_EMAIL))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Username is not valid format.';
						}

						if($this->api_type == 'sandbox')
							$userExist = User::where('username', $request->username)
																	->where('id', '<>', $request->user_id)
																	->where('api_type', $this->api_type)
																	->first();
						else
							$userExist = User::where('username', $request->username)
																	->where('id', '<>', $request->user_id)
																	->where(function($query) {
																			$query->whereNull('api_type')
																						->orWhere('api_type', $this->api_type);
																	})
																	->first();

						if($userExist)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Username is already exists. Please choose another.';
						}
				}

				// Generate the password if the user didn't supply one.
				if ($request->has('password')) {
					$password = $request->password;
				} else {
					$password = str_random(12);
				}

				$user = User::where('id', $request->user_id)
											->where('api_type', $this->api_type)
											->first();

				if(!$user)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'User ID: ' . $request->user_id . ' is not found.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$user->name = $request->name;
				$user->username = $request->username;
				$user->email = $request->email;
				$user->password = bcrypt($password);
				$user->account_type = 3;
				$user->api_type = $this->api_type;
				$user->save();

				User_Setting::where('user_id', $request->user_id)
											->where('name', 'like', 'permission.%')
											->forceDelete();

				if ($request->has('permission')) {
					foreach ($request->input('permission') as $permission => $value) {
						$settings = new User_Setting();
						$settings->user_id = $user->id;
						$settings->name = "permission." . $permission;
						$settings->value = $value;
						$settings->save();
					}
				}

				$currentUser = $this->user;
				Mail::send('Settings.staffEmail', [
					'name' => $user->name,
					'username' => $user->username,
					'password' => $password,
					'siteURL' => $user->siteSettings('url'),
					'siteName' => $user->siteSettings('name')
				], function ($m) use ($user, $currentUser) {
					$m->from($currentUser->email, $currentUser->name);
					$m->to($user->email, $user->name);
					$m->subject('BillingServ Account Details');
				});

				return Response::json([
						'success' => true,
						'user_id' => $user->id,
						'message' => 'Staff updated successfully',
				], 200);
		}

		/* Lists Staff
			 Params
			 Header:
				- token: string (required)
		*/
		public function listsStaff(Request $request)
		{
			if($this->api_type == 'sandbox')
				$users = User::where('api_type', $this->api_type)
														->orderBy('id')
														->get();
			else
				$users = User::where(function($query) {
																$query->whereNull('api_type')
																			->orWhere('api_type', $this->api_type);
														})
														->orderBy('id')
														->get();

				$usersArr = [];
				foreach($users as $user)
				{
						$userObj = new \stdClass();
						$userObj->id = $user->id;
						$userObj->name = $user->name;
						$userObj->username = $user->username;
						$userObj->email = $user->email;
						$userObj->account_type = $user->account_type;
						$userObj->last_login = $user->last_login;
						$userObj->stripeId = $user->stripeId;
						$userObj->vat_number = $user->vat_number;
						$userObj->fraudlabs_status = $user->fraudlabs_status;
						$userObj->fraudlabs_json = $user->fraudlabs_json;
						$userObj->sandbox_api_key = $user->sandbox_api_key;
						$userObj->live_api_key = $user->live_api_key;
						$userObj->api_type = $user->api_type;
						$userObj->settings = $user->settings;
						$userObj->created_at = $user->created_at;
						$userObj->updated_at = $user->updated_at;
						$userObj->deleted_at = $user->deleted_at;
						$usersArr[] = $userObj;
				}

				return Response::json([
						'success' => true,
						'staffs' => $usersArr
				], 200);
		}

		/* Get Staff by ID
			 Params
			 Header:
				- token: string (required)

			 Body:
				- user_id: integer (required)
		*/
		public function getStaffById(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('user_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'User ID is required.';
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
					$user = User::where('id', $request->user_id)
												->where('api_type', $this->api_type)
												->orderBy('id')
												->first();
				else
					$user = User::where('id', $request->user_id)
												->where(function($query) {
														$query->whereNull('api_type')
																	->orWhere('api_type', $this->api_type);
												})
												->orderBy('id')
												->first();

				$userObj = new \stdClass();
				if($user)
				{
						$userObj->id = $user->id;
						$userObj->name = $user->name;
						$userObj->username = $user->username;
						$userObj->email = $user->email;
						$userObj->account_type = $user->account_type;
						$userObj->last_login = $user->last_login;
						$userObj->stripeId = $user->stripeId;
						$userObj->vat_number = $user->vat_number;
						$userObj->fraudlabs_status = $user->fraudlabs_status;
						$userObj->fraudlabs_json = $user->fraudlabs_json;
						$userObj->sandbox_api_key = $user->sandbox_api_key;
						$userObj->live_api_key = $user->live_api_key;
						$userObj->api_type = $user->api_type;
						$userObj->settings = $user->settings;
						$userObj->created_at = $user->created_at;
						$userObj->updated_at = $user->updated_at;
						$userObj->deleted_at = $user->deleted_at;
				}

				return Response::json([
						'success' => true,
						'staffs' => $userObj
				], 200);
		}

		/* Delete Staff
			 Params
			 Header:
				- token: string (required)

			 Body:
				- user_id: integer (required)
		*/
		public function deleteStaff(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('user_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'User ID is required.';
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
					$user = User::where('id', $request->user_id)
												->where('api_type', $this->api_type)
												->orderBy('id')
												->first();
				else
					$user = User::where('id', $request->user_id)
												->where(function($query) {
														$query->whereNull('api_type')
																	->orWhere('api_type', $this->api_type);
												})
												->orderBy('id')
												->first();
				if($user)
				{
						$user->delete();
				}

				return Response::json([
						'success' => true,
						'message' => 'Staff ID: ' . $request->id . ' deleted successfully',
				], 200);
		}

		/* Invoice Setting
			 Params
			 Header:
				- token: string (required)

			 Body:
				- orderReturnURL: string (url) (optional)
 				- prefix: string (optional)
				- startNumber: number (optional)
				- paymentsDue: integer (optional)
				- lateFees: integer (min:0) (optional)
				- lateFeesTax: integer (optional)
				- reminders: integer (min:1) (optional)
				- reminders4suspend: integer (min:1) (optional)
				- days2send: integer (min:1) (optional)
				- total-order[0]: fixeddiscount (optional)
				- total-order[1]: discountcode (optional)
				- total-order[2]: customtotals (optional)
				- total-order[3]: shipping (optional)
				- total-order[4]: tax (optional)
				- defaultCurrency: integer (optional)
				- currency[]: integer (can be more than 1) (optional)
				- exchangerate: double (optional)
				- vat[name]: string (optional)
				- vat[number]: string (optional)
				- companyRegistration: string (optional)
				- fixedDiscount: 1 (optional)
				- discountCode: 1 (optional)

				* total-order can be shuffle based on order
		*/
		public function invoiceSetting(Request $request)
		{
			$errorMessage = '';
			if($request->has('orderReturnURL'))
			{
					if(filter_var($request->orderReturnURL, FILTER_VALIDATE_URL))
					{
							if($errorMessage != '') $errorMessage .= ', ';
							$errorMessage .= 'Exchange Rate format is invalid.';
					}
			}

			if($request->has('startNumber') && !is_numeric($request->startNumber) || $request->startNumber < 0)
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'StartNumber format is invalid.';
			}

			if($request->has('paymentsDue') && !is_numeric($request->paymentsDue) || $request->paymentsDue < 0)
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'PaymentsDue format is invalid.';
			}

			if($request->has('lateFees') && !is_numeric($request->lateFees) || $request->lateFees < 0)
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'LateFees format is invalid.';
			}

			if($request->has('lateFeesTax') && !is_numeric($request->lateFeesTax) || $request->lateFeesTax < 0)
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'LateFeesTax format is invalid.';
			}

			if($request->has('reminders') && !is_numeric($request->reminders) || $request->reminders <= 0)
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'Reminders format is invalid.';
			}

			if($request->has('reminders4suspend') && !is_numeric($request->reminders4suspend) || $request->reminders4suspend <= 0)
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'Reminders4Suspend format is invalid.';
			}

			if($request->has('days2send') && !is_numeric($request->days2send) || $request->days2send < 0)
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'Days2Send format is invalid.';
			}

			if($request->has('exchangerate') && !is_numeric($request->exchangerate) || $request->exchangerate < 0)
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'Exchange Rate format is invalid.';
			}

			if($request->has('defaultCurrency') && !is_numeric($request->defaultCurrency) || $request->defaultCurrency < 0)
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'Default Currency format is invalid.';
			}

			if($errorMessage != '')
			{
					return Response::json([
																		'success' => false,
																		'errors' => $errorMessage
																],
																401);
			}

			$settings = User_Setting::where('user_id', $this->user->id)->where('name', 'LIKE', 'invoice.%')->delete();

			$values = [];
			$insertTime = date('Y-m-d H:i:s');

			foreach ($request->all() as $setting => $value)
			{
				if($setting === 'defaultCurrency')
				{
					$values[] = [
						'name' => "site.defaultCurrency",
						'value' => $value,
						'user_id' => $this->user->id,
						'created_at' => $insertTime,
						'updated_at' => $insertTime
					];
				}
				elseif (is_array($value))
				{
					foreach ($value as $subSetting => $subvalue)
					{
						$values[] = [
							'name' => "invoice.{$setting}.{$subSetting}",
							'value' => $subvalue,
							'user_id' => $this->user->id,
							'created_at' => $insertTime,
							'updated_at' => $insertTime
						];
	        }
	      }
				else
				{
		          $values[] = [
							'name' => "invoice.{$setting}",
							'value' => $value,
							'user_id' => $this->user->id,
							'created_at' => $insertTime,
							'updated_at' => $insertTime
						];
	       }
	    }
			foreach ($values as $val)
			{
          $user_setting = User_Setting::where([['user_id',$val['user_id']],['name',$val['name']]])->first();
          if (!empty($user_setting))
					{
              $user_setting->value = $val['value'];
              $user_setting->updated_at = $val['updated_at'];
              $user_setting->save();
          }
					else
					{
              User_Setting::create($val);
          }
	     }

			 return Response::json([
					 'success' => true,
					 'message' => 'Invoice settings set successfully',
			 ], 200);
		}

		/* Invoice Setting
			 Params
			 Header:
				- token: string (required)

			 Body:
				- user_id: integer (required)
		*/
		public function getInvoiceSetting(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('user_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'User ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$use_settings = User_Setting::where('user_id', $request->user_id)
																			->where('name', 'like', 'invoice.%')
																			->get();

				return Response::json([
 					 'success' => true,
 					 'user_settings' => $use_settings,
 			 ], 200);
		}

		/* Create Tax Zone
			 Params
			 Header:
				- token: string (required)

			 Body:
 				- name: string (required)
 				- counties[]: integer (required)
		*/
		public function createTaxZone(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('counties'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Counties is required.';
				}
				else if(!is_array($request->counties))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Counties is not valid format.';
				}

				if(is_array($request->counties))
				{
						foreach($request->counties as $k=>$v)
						{
								$county = Counties::find($v);
								if(!$county)
								{
										if($errorMessage != '') $errorMessage .= ', ';
										$errorMessage .= 'Counties is not valid value.';
										break;
								}
						}
				}

				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$zone = new TaxZones();
				$zone->user_id = $this->user->id;
				$zone->name = $request->name;
				$zone->api_type = $this->api_type;
				$zone->save();

				foreach($request->counties as $k=>$v)
				{
						$TaxZoneCounty = new TaxZoneCounties();
						$TaxZoneCounty->zone_id = $zone->id;
						$TaxZoneCounty->county_id = $v;
						$TaxZoneCounty->save();
				}

				return Response::json([
 					 'success' => true,
					 'zone_id' => $zone->id,
 					 'message' => 'Tax zone created successfully',
 			 ], 200);
		}

		/* Update Tax Zone
			 Params
			 Header:
				- token: string (required)

			 Body:
			 	- zone_id: integer (required)
 				- name: string (required)
 				- counties[]: integer (required)
		*/
		public function updateTaxZone(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('zone_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Zone ID is required.';
				}

				if(!$request->has('counties'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Counties is required.';
				}
				else if(!is_array($request->counties))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Counties is not valid format.';
				}

				if(is_array($request->counties))
				{
						foreach($request->counties as $k=>$v)
						{
								$county = Counties::find($v);
								if(!$county)
								{
										if($errorMessage != '') $errorMessage .= ', ';
										$errorMessage .= 'Counties is not valid value.';
										break;
								}
						}
				}

				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}

				if($this->api_type == 'sandbox')
					$zone = TaxZones::where('id', $request->zone_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$zone = TaxZones::where('id', $request->zone_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$zone)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Tax Zone ID ' . $request->zone_id . ' is not found.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$zone->user_id = $this->user->id;
				$zone->name = $request->name;
				$zone->api_type = $this->api_type;
				$zone->save();

				TaxZoneCounties::where('zone_id', $zone->id)->forceDelete();
				foreach($request->counties as $k=>$v)
				{
						$TaxZoneCounty = new TaxZoneCounties();
						$TaxZoneCounty->zone_id = $zone->id;
						$TaxZoneCounty->county_id = $v;
						$TaxZoneCounty->save();
				}

				return Response::json([
 					 'success' => true,
					 'zone_id' => $zone->id,
 					 'message' => 'Tax zone updated successfully',
 			 ], 200);
		}

		/* Delete Tax Zone
			 Params
			 Header:
				- token: string (required)

			 Body:
			 	- zone_id: integer (required)
		*/
		public function deleteTaxZone(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('zone_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Zone ID is required.';
				}

				if($this->api_type == 'sandbox')
					$zone = TaxZones::where('id', $request->zone_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$zone = TaxZones::where('id', $request->zone_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$zone)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Tax Zone ID ' . $request->zone_id . ' is not found.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$zone->delete();

				return Response::json([
 					 'success' => true,
 					 'message' => 'Tax zone ID: ' . $zone->id . ' deleted successfully',
 			 ], 200);
		}

		/* Lists Tax Zone
			 Params
			 Header:
				- token: string (required)
		*/
		public function listsTaxZone(Request $request)
		{
				if($this->api_type == 'sandbox')
					$zones = TaxZones::where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->orderBy('id')
															->get();
				else
					$zones = TaxZones::where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->orderBy('id')
															->get();

				$zoneArr = [];
				foreach($zones as $zone)
				{
						$zoneObj = new \stdClass();
						$zoneObj->id = $zone->id;
						$zoneObj->user = $zone->user;
						$zoneObj->api_type = $zone->api_type;
						$zoneObj->created_at = $zone->created_at;
						$zoneObj->updated_at = $zone->updated_at;
						$zoneObj->deleted_at = $zone->deleted_at;

						$zoneCounties = TaxZoneCounties::where('zone_id', $zone->id)
																						->orderBy('id')
																						->get();

						$zoneObj->zone_counties = $zoneCounties;
						$zoneArr[] = $zoneObj;
				}

				return Response::json([
 					 'success' => true,
 					 'tax_zones' => $zoneArr,
 			 ], 200);
		}

		/* Get Tax Zone by ID
			 Params
			 Header:
				- token: string (required)

			 Body:
			  - zone_id: integer (required)
		*/
		public function getTaxZoneById(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('zone_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Zone ID is required.';
				}

				if($this->api_type == 'sandbox')
					$zone = TaxZones::where('id', $request->zone_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$zone = TaxZones::where('id', $request->zone_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$zone)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Tax Zone ID ' . $request->zone_id . ' is not found.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$zoneObj = new \stdClass();
				$zoneObj->id = $zone->id;
				$zoneObj->user = $zone->user;
				$zoneObj->api_type = $zone->api_type;
				$zoneObj->created_at = $zone->created_at;
				$zoneObj->updated_at = $zone->updated_at;
				$zoneObj->deleted_at = $zone->deleted_at;

				$zoneCounties = TaxZoneCounties::where('zone_id', $zone->id)
																				->orderBy('id')
																				->get();

				$zoneObj->zone_counties = $zoneCounties;

				return Response::json([
 					 'success' => true,
 					 'tax_zone' => $zoneObj,
 			 ], 200);
		}

		/* Create Tax Class
			 Params
			 Header:
				- token: string (required)

			 Body:
 				- name: string (required)
 				- rate[{zone_id}]: {rate} double ({zone_id} is integer) (required)
 				- default: integer (0 = not default | 1 = default) (required)

			* rate[{zone_id}]: {rate} can be more than one
		*/
		public function createTaxClass(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}

				if($request->has('rate'))
				{
						if(!is_array($request->rate))
						{
							if($errorMessage != '') $errorMessage .= ', ';
							$errorMessage .= 'Rate is not array format.';
						}
						else
						{
								foreach($request->rate as $k => $v)
								{
										if(!is_numeric($k))
										{
												if($errorMessage != '') $errorMessage .= ', ';
												$errorMessage .= 'Rate is not a number.';
												break;
										}

										if($this->api_type == 'sandbox')
											$zone = TaxZones::where('id', $k)
																					->where('user_id', $this->user->id)
																					->where('api_type', $this->api_type)
																					->first();
										else
											$zone = TaxZones::where('id', $k)
																					->where('user_id', $this->user->id)
																					->where(function($query) {
																							$query->whereNull('api_type')
																										->orWhere('api_type', $this->api_type);
																					})
																					->first();

										if(!$zone)
										{
												if($errorMessage != '') $errorMessage .= ', ';
												$errorMessage .= 'Rate is not a valid value.';
												break;
										}

										if(!is_double(floatval($v)))
										{
												if($errorMessage != '') $errorMessage .= ', ';
												$errorMessage .= 'Rate is not a double format.';
												break;
										}
								}
						}
				}

				if($request->has('default'))
				{
						if($request->default < 0 || $request->default > 1)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Default is not valid value.';
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

				$default = '0';
				if($request->has('default')) $default = $request->default;

				if($default == '1')
				{
						TaxClasses::where('id', '<>', -1)->update(['default' => 1]);
				}

				$class = new TaxClasses();
				$class->user_id = $this->user->id;
				$class->name = $request->name;
				$class->default = $default;
				$class->api_type = $this->api_type;
				$class->save();

				foreach($request->rate as $k => $v)
				{
						$rate = new TaxRates();
						$rate->zone_id = $k;
						$rate->class_id = $class->id;
						$rate->rate = $v;
						$rate->save();
				}

				return Response::json([
 					 'success' => true,
					 'class_id' => $class->id,
 					 'message' => 'Tax class created successfully',
 			 ], 200);
		}

		/* Update Tax Class
			 Params
			 Header:
				- token: string (required)

			 Body:
			  - class_id: integer (required)
 				- name: string (required)
 				- rate[{zone_id}]: {rate} double ({zone_id} is integer) (required)
 				- default: integer (0 = not default | 1 = default) (required)

			* rate[{zone_id}]: {rate} can be more than one
		*/
		public function updateTaxClass(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('class_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Class ID is required.';
				}

				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}

				if($request->has('rate'))
				{
						if(!is_array($request->rate))
						{
							if($errorMessage != '') $errorMessage .= ', ';
							$errorMessage .= 'Rate is not array format.';
						}
						else
						{
								foreach($request->rate as $k => $v)
								{
										if(!is_numeric($k))
										{
												if($errorMessage != '') $errorMessage .= ', ';
												$errorMessage .= 'Rate is not a number.';
												break;
										}

										if($this->api_type == 'sandbox')
											$zone = TaxZones::where('id', $k)
																					->where('user_id', $this->user->id)
																					->where('api_type', $this->api_type)
																					->first();
										else
											$zone = TaxZones::where('id', $k)
																					->where('user_id', $this->user->id)
																					->where(function($query) {
																							$query->whereNull('api_type')
																										->orWhere('api_type', $this->api_type);
																					})
																					->first();

										if(!$zone)
										{
												if($errorMessage != '') $errorMessage .= ', ';
												$errorMessage .= 'Tax Zone ID: ' . $k . ' is not found.';
												break;
										}

										if(!is_double(floatval($v)))
										{
												if($errorMessage != '') $errorMessage .= ', ';
												$errorMessage .= 'Rate is not a double format.';
												break;
										}
								}
						}
				}

				if($request->has('default'))
				{
						if($request->default < 0 || $request->default > 1)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Default is not valid value.';
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

				if($this->api_type == 'sandbox')
					$class = TaxClasses::where('id', $request->class_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$class = TaxClasses::where('id', $request->class_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$class)
				{
					return Response::json([
																		'success' => false,
																		'errors' => 'Tax Class ID: ' . $request->class_id . ' is not found.'
																],
																401);
				}

				$default = '0';
				if($request->has('default')) $default = $request->default;

				if($default == '1')
				{
						TaxClasses::where('id', '<>', -1)->update(['default' => 1]);
				}

				$class->user_id = $this->user->id;
				$class->name = $request->name;
				$class->default = $default;
				$class->api_type = $this->api_type;
				$class->save();

				TaxRates::where('class_id', $class->id)->forceDelete();
				foreach($request->rate as $k => $v)
				{
						$rate = new TaxRates();
						$rate->zone_id = $k;
						$rate->class_id = $class->id;
						$rate->rate = $v;
						$rate->save();
				}

				return Response::json([
 					 'success' => true,
					 'class_id' => $class->id,
 					 'message' => 'Tax class updated successfully',
 			 ], 200);
		}

		/* Delete Tax Class
			 Params
			 Header:
				- token: string (required)

			 Body:
			  - class_id: integer (required)
		*/
		public function deleteTaxClass(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('class_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Class ID is required.';
				}

				if($this->api_type == 'sandbox')
					$class = TaxClasses::where('id', $request->class_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$class = TaxClasses::where('id', $request->class_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$class)
				{
					return Response::json([
																		'success' => false,
																		'errors' => 'Tax Class ID: ' . $request->class_id . ' is not found.'
																],
																401);
				}

				$class->delete();

				return Response::json([
 					 'success' => true,
 					 'message' => 'Tax class ID: ' . $request->class_id . ' deleted successfully',
 			 ], 200);
		}

		/* Lists Tax Class
			 Params
			 Header:
				- token: string (required)
		*/
		public function listsTaxClass(Request $request)
		{
			$errorMessage = '';

			if(!$request->has('class_id'))
			{
					if($errorMessage != '') $errorMessage .= ', ';
					$errorMessage .= 'Class ID is required.';
			}

			if($this->api_type == 'sandbox')
				$class = TaxClasses::where('id', $request->class_id)
														->where('user_id', $this->user->id)
														->where('api_type', $this->api_type)
														->first();
			else
				$class = TaxClasses::where('id', $request->class_id)
														->where('user_id', $this->user->id)
														->where(function($query) {
																$query->whereNull('api_type')
																			->orWhere('api_type', $this->api_type);
														})
														->first();

			if(!$class)
			{
				return Response::json([
																	'success' => false,
																	'errors' => 'Tax Class ID: ' . $request->class_id . ' is not found.'
															],
															401);
			}

				$classArr = [];
				foreach($classes as $class)
				{
						$classObj = new \stdClass();
						$classObj->id = $class->id;
						$classObj->user_id = $class->user_id;
						$classObj->api_type = $class->api_type;
						$classObj->created_at = $class->created_at;
						$classObj->updated_at = $class->updated_at;
						$classObj->deleted_at = $class->deleted_at;
						$classObj->default = $class->default;
						$classObj->rates = $class->taxRate;
						$classArr[] = $classObj;
				}

				return Response::json([
 					 'success' => true,
 					 'tax_classes' => $classArr,
 			 ], 200);
		}

		/* Get Tax Class by ID
			 Params
			 Header:
				- token: string (required)

			 Body:
				- class_id: integer (required)
		*/
		public function getTaxClassById(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('class_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Class ID is required.';
				}

				if($this->api_type == 'sandbox')
					$class = TaxClasses::where('id', $request->class_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$class = TaxClasses::where('id', $request->class_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$class)
				{
					return Response::json([
																		'success' => false,
																		'errors' => 'Tax Class ID: ' . $request->class_id . ' is not found.'
																],
																401);
				}

				$classObj = new \stdClass();
				$classObj->id = $class->id;
				$classObj->user_id = $class->user_id;
				$classObj->api_type = $class->api_type;
				$classObj->created_at = $class->created_at;
				$classObj->updated_at = $class->updated_at;
				$classObj->deleted_at = $class->deleted_at;
				$classObj->default = $class->default;
				$classObj->rates = $class->taxRate;

				return Response::json([
 					 'success' => true,
 					 'tax_class' => $classObj,
 			 ], 200);
		}

}
