<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Address;
use App\Currency;
use App\Counties;
use App\Countries;
use App\Integration;
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

class ModuleController extends Controller
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

		/* Activate / Deactivate Module
			 Params
			 Header:
				- token: string (required)

				Body:
				- module: string (required)
				- status: integer (1 => active | 2 => deactivate) (required)
		*/
		public function activateDeactivateModule(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('module'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Module is required.';
				}

				if(!$request->has('status'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Status is required.';
				}

				if($request->status)
				{
						if($request->status < 0 || $request->status > 1)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Status is not valid format.';
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

				$module = $request->module;
				$status = $request->status;

				$userSetting = User_Setting::where('name', 'integration.' . $module)
																		->where('user_id', $this->user->id)
																		->first();

				if(!$userSetting) $userSetting = new User_Setting();
				$userSetting->user_id = $this->user->id;
				$userSetting->name = 'integration.' . $module;
				$userSetting->value = $status;
				$userSetting->save();

				return Response::json([
						'success' => true,
						'message' => 'Module: ' . $module . ' set ' . $status . ' successfully'
				], 200);
		}

		/* Get Module Configuration
			 Params
			 Header:
				- token: string (required)

				Body:
				- module: string (required)
		*/
		public function getModuleConfiguration(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('module'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Module is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$module = $request->module;

				$userSettings = User_Setting::where(function($query) use ($module) {
																				$query->where('name', 'integration.' . $module)
																							->orWhere('name', 'like', $module . '%');
																		})
																		->where('user_id', $this->user->id)
																		->get();

				$integrations = Integration::where('integration_type', $module)
																		->get();

				$module = new \stdClass();
				$module->settings = $userSettings;
				$module->integrations = $integrations ?: [];

				return Response::json([
						'success' => true,
						'module' => $module
				], 200);
		}
}
