<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Address;
use App\Counties;
use App\Countries;
use App\Invoice;
use App\MiscStorage;
use App\User;
use App\User_Contact;
use App\User_Link;
use App\SavedPaymentMethods;
use App\Mail\GeneralEmail;
use Auth;
use Hash;
use Illuminate\Support\Facades\Validator;
use Permissions;
use Mail;
use Response;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use FraudLabsPro\Configuration as FraudConfiguration;
use FraudLabsPro\Order as FraudOrder;

class CustomerController extends Controller
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

		/* Create Customer
			 Params
			 Header:
				- token: string (required)

				Body:
				- name: string (required)
				- address_1: string (required)
				- address_2: string (optional)
				- city: string (required)
				- county_id: integer (required)
				- country_id: integer (required)
				- postal_code: string (required)
				- phone: string (required)
				- fax: string (optional)
				- username: string (email format) (required)
				- password: string (required)
				- website: string (url format) (optional)
				- credit: integer (optional)
				- business_name: string (optional)
				- password: string (required)
				- comment: string (optional)
		*/
		public function createCustomer(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}
				if(!$request->has('address_1'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Address_1 is required.';
				}
				if(!$request->has('city'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'City is required.';
				}
				if(!$request->has('county_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'County is required.';
				}
				if(!$request->has('country_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Country is required.';
				}
				if(!$request->has('postal_code'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Postal Code is required.';
				}
				if(!$request->has('phone'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Phone is required.';
				}
				if(!$request->has('username'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Username is required.';
				}
				if(!$request->has('password'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Password is required.';
				}

				if($request->username)
				{
						if(!filter_var($request->username, FILTER_VALIDATE_EMAIL))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Username is not valid format.';
						}
						$userExist = User::where('username', $request->username)
																->orWhere('email', $request->username)
																->first();
						if($userExist)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Username is already exists. Please choose another.';
						}
				}

				$county_id = $request->county_id;
				$country_id = $request->country_id;

				$country = Countries::find($country_id);
				if(!$country)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Country ID: '. $country_id . ' not found.';
				}

				if($country)
				{
						$county = Counties::where('country_id', $country_id)
																->where('id', $county_id)
																->first();

						if(!$county)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'County ID: '. $county_id . ' not found.';
						}
				}

				if($request->has('website'))
				{
						if(!filter_var($request->website, FILTER_VALIDATE_URL))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Website is not valid format.';
						}
				}

				if($request->has('credit') && !is_numeric($request->credit))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Credit is not valid format.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$name = $request->name;
				$username = $request->username;
				$password = $request->password;

				$user = User::create([
					'name' => $name,
					'email' => $username,
					'username' => $username,
					'password' => Hash::make($password),
					'account_type' => User::CUSTOMER,
					'api_type' => $this->api_type,
				]);

				$business_name = '';
				if($request->business_name) $business_name = $request->business_name;

				$address_1 = $request->address_1;

				$address_2 = '';
				if($request->address_2) $address_2 = $request->address_2;

				$city = $request->city;
				$postal_code = $request->postal_code;
				$phone = $request->phone;

				$fax = '';
				if($request->fax) $fax = $request->fax;

				$website = $request->website;

				$address = Address::create([
					'business_name' => $business_name,
					'address_1' => $address_1,
					'address_2' => $address_2,
					'city' => $city,
					'county_id' => $county_id,
					'country_id' => $country_id,
					'postal_code' => $postal_code,
					'phone' => $phone,
					'fax' => $fax,
					'website' => $website,
					'email' => $user->email,
				]);

				if (!$address) {
					abort('Not created address');
				}

				$contact = User_Contact::create([
					'address_id' => $address->id,
					'user_id' => $user->id,
					'type' => User_Contact::MAILING,
				]);

				$link = User_Link::create([
					'user_id' => $user->id,
					'parent_id' => $this->user->id,
				]);

				if ($request->has('comment')) {
					$notes = MiscStorage::create([
						'name' => "customer-notes-{$user->id}",
						'user_id' => $this->user->id,
						'value' => $request->comment,
					]);
				}

				if ($request->has('credit')) {
					$notes = MiscStorage::create([
						'name' => "account-credit",
						'user_id' => $user->id,
						'value' => $request->credit,
					]);
				}

				$userFrom = $this->user;
				$subject = 'customer created';
				$content = '--customer data--';
				$view = 'Customers.mail.customerCreateEmail';
				Mail::to($user)->send(new GeneralEmail($userFrom, $subject, $content, $view));

				return Response::json([
						'success' => true,
						'message' => 'User: ' . $user->id . ' created successfully',
				], 200);
		}

		/* Update Customer
			 Params
			 Header:
				- token: string (required)

				Body:
				- id: integer (required)
				- name: string (required)
				- address_1: string (required)
				- address_2: string (optional)
				- city: string (required)
				- county_id: integer (required)
				- country_id: integer (required)
				- postal_code: string (required)
				- phone: string (required)
				- fax: string (optional)
				- username: string (email format) (required)
				- password: string (required)
				- website: string (url format) (optional)
				- credit: integer (optional)
				- business_name: string (optional)
				- password: string (required)
				- comment: string (optional)
		*/
		public function updateCustomer(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}
				if(!$request->has('name'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Name is required.';
				}
				if(!$request->has('address_1'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Address_1 is required.';
				}
				if(!$request->has('city'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'City is required.';
				}
				if(!$request->has('county_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'County is required.';
				}
				if(!$request->has('country_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Country is required.';
				}
				if(!$request->has('postal_code'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Postal Code is required.';
				}
				if(!$request->has('phone'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Phone is required.';
				}
				if(!$request->has('username'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Username is required.';
				}
				if(!$request->has('password'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Password is required.';
				}

				$id = $request->id;
				$user = User::where('id', $id)
											->where('api_type', $this->api_type)
											->first();
				if(!$user)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'User not found.';
				}

				if($request->username)
				{
						if(!filter_var($request->username, FILTER_VALIDATE_EMAIL))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Username is not valid format.';
						}

						$userExist = User::where('username', $request->username)
															->where('id', '<>', $id)
															->first();
						if($userExist)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Username is already exists. Please choose another.';
						}
				}

				$county_id = $request->county_id;
				$country_id = $request->country_id;

				$country = Countries::find($country_id);
				if(!$country)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Country ID: '. $country_id . ' not found.';
				}

				if($country)
				{
						$county = Counties::where('country_id', $country_id)
																->where('id', $county_id)
																->first();

						if(!$county)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'County ID: '. $county_id . ' not found.';
						}
				}

				if($request->has('website'))
				{
						if(!filter_var($request->website, FILTER_VALIDATE_URL))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Website is not valid format.';
						}
				}

				if($request->has('credit') && !is_numeric($request->credit))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Credit is not valid format.';
				}


				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$name = $request->name;
				$username = $request->username;
				$password = $request->password;

				$user->name = $name;
				$user->email = $username;
				$user->username = $username;
				if ($request->has('password')) {
					$user->password = Hash::make($password);
				}

				$user->api_type = $this->api_type;

				$business_name = '';
				if($request->business_name) $business_name = $request->business_name;

				$address_1 = $request->address_1;

				$address_2 = '';
				if($request->address_2) $address_2 = $request->address_2;

				$city = $request->city;
				$postal_code = $request->postal_code;
				$phone = $request->phone;

				$fax = '';
				if($request->fax) $fax = $request->fax;

				$website = $request->website;

				if ($user->mailingContact !== null) {
					$user->mailingContact->address->business_name = $business_name;
					$user->mailingContact->address->address_1 = $address_1;
					$user->mailingContact->address->address_2 = $address_2;
					$user->mailingContact->address->city = $city;
					$user->mailingContact->address->county_id = $county_id;
					$user->mailingContact->address->country_id = $country_id;
					$user->mailingContact->address->postal_code = $postal_code;
					$user->mailingContact->address->phone = $phone;
					$user->mailingContact->address->fax = $fax;
					$user->mailingContact->address->website = $website;
					$user->mailingContact->address->email = $user->email;
				} else {
					$address = new Address();
					$address->business_name = $business_name;
					$address->address_1 = $address_1;
					$address->address_2 = $address_2;
					$address->city = $city;
					$address->county_id = $county_id;
					$address->country_id = $country_id;
					$address->postal_code = $postal_code;
					$address->phone = $phone;
					$address->fax = $fax;
					$address->website = $website;
					$address->email = $user->email;
					$address->save();

					$contact = new User_Contact();
					$contact->address_id = $address->id;
					$contact->user_id = $user->id;
					$contact->type = User_Contact::MAILING;
					$contact->save();
				}

				$user->push();

				$vat = $user->settings()->where('name', 'billing.vat')->first();
				if ($request->has('vat')) {
					if (empty($vat)) {
						$user->settings()->create([
							'name' => 'billing.vat',
							'value' => true
						]);
					} else {
						$vat->value = true;
						$vat->save();
					}
				} elseif (!empty($vat)) {
					$vat->value = false;
					$vat->save();
				}

				$notes = MiscStorage::where('name', 'customer-notes-' . $user->id)->get();
				if ($notes->count() === 0) {
					$notes = new MiscStorage;
					$notes->name = "customer-notes-{$user->id}";
					$notes->user_id = $this->user->id;
				} else {
					$notes = $notes->first();
				}

				$credit = MiscStorage::where('name', 'account-credit')->where('user_id', $user->id)->first();

				if(!$credit) {
					$credit = new MiscStorage;
					$value = 0;
				} else {
					$value = $credit->value + $request->credit;
				}

				$credit->name = "account-credit";
				$credit->user_id = $user->id;
				$credit->value = $value;
				$credit->save();

				return Response::json([
						'success' => true,
						'message' => 'User: ' . $user->id . ' updated successfully',
				], 200);
		}

		/* Lists Customer
			 Params
			 Header:
			 	- token: string (required)
		*/
		public function listsCustomer(Request $request)
		{
				if($this->api_type == 'sandbox')
					$users = User::where('api_type', $this->api_type)->orderBy('id')->get();
				else
					$users = User::whereNull('api_type')
                        ->orWhere('api_type', $this->api_type)
                        ->orderBy('id')
                        ->get();

				$userArr = [];
				foreach($users as $user)
				{
						$userObj = new \stdClass();
						$userObj->id = $user->id;
						$userObj->name = $user->name;
						$userObj->username = $user->username;
						$userObj->email = $user->email;
						$userObj->account_type = $user->account_type;
						$userObj->created_at = $user->created_at;
						$userObj->updated_at = $user->updated_at;
						$userObj->deleted_at = $user->deleted_at;
						$userObj->default_contact = $user->defaultContact ? $user->defaultContact->address : null;
						$userObj->mailing_contact = $user->mailingContact ? $user->mailingContact->address : null;
						$userObj->admin_contact = $user->adminContact ? $user->adminContact->address : null;
						$userObj->tech_contact = $user->techContact ? $user->techContact->address : null;

						$userArr[] = $userObj;
				}

				return Response::json([
						'success' => true,
						'users' => $userArr
				], 200);
		}

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
		public function checkByUsernameAndPassword(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->all(),
                ], 422);
            }

            if (auth()->attempt($request->only('username', 'password'))) {
                $user = auth()->user();

                $userObj = new \stdClass();
                $userObj->id = $user->id;
                $userObj->name = $user->name;
                $userObj->username = $user->username;
                $userObj->email = $user->email;
                $userObj->account_type = $user->account_type;
                $userObj->created_at = $user->created_at;
                $userObj->updated_at = $user->updated_at;
                $userObj->default_contact = $user->defaultContact ? $user->defaultContact->address : null;
                $userObj->mailing_contact = $user->mailingContact ? $user->mailingContact->address : null;
                $userObj->admin_contact = $user->adminContact ? $user->adminContact->address : null;
                $userObj->tech_contact = $user->techContact ? $user->techContact->address : null;

                return response()->json([
                    'success' => true,
                    'user' => $userObj,
                ]);
            }

            return response()->json([
                'success' => false,
                'errors' => ['These credentials do not match our records.']
            ], 422);
        }

		/* Get Customer
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				- id: integer (required)
		*/
		public function getCustomer(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;

				if($this->api_type == 'sandbox')
					$user = User::where('id', $id)
																			->where('api_type', $this->api_type)
																			->first();
				else
					$user = User::where('id', $id)
																			->where(function($query) {
																					$query->whereNull('api_type')
																								->orWhere('api_type', $this->api_type);
																			})
																			->first();

				if(!$user)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'User ID: ' . $id . ' not found.'
																	],
																	401);
				}

				$userObj = new \stdClass();
				$userObj->id = $user->id;
				$userObj->name = $user->name;
				$userObj->username = $user->username;
				$userObj->email = $user->email;
				$userObj->account_type = $user->account_type;
				$userObj->created_at = $user->created_at;
				$userObj->updated_at = $user->updated_at;
				$userObj->deleted_at = $user->deleted_at;
				$userObj->default_contact = $user->defaultContact ? $user->defaultContact->address : null;
				$userObj->mailing_contact = $user->mailingContact ? $user->mailingContact->address : null;
				$userObj->admin_contact = $user->adminContact ? $user->adminContact->address : null;
				$userObj->tech_contact = $user->techContact ? $user->techContact->address : null;

				return Response::json([
						'success' => true,
						'user' => $userObj
				], 200);
		}

		/* Delete Customer
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	id: integer (required)
		*/
		public function DeleteCustomer(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				if($this->api_type == 'sandbox')
					$user = User::where('id', $id)
																->where('api_type', $this->api_type)
																->first();
				else
					$user = User::where('id', $id)
																->where(function($query) {
										                $query->whereNull('api_type')
										                      ->orWhere('api_type', $this->api_type);
										            })
																->first();

					if(!$user)
					{
							return Response::json([
																				'success' => false,
																				'errors' => 'User ID: ' . $id . ' not found.'
																		],
																		401);
					}

					$user->delete();

					return Response::json([
							'success' => true,
							'message' => 'User: ' . $id . ' deleted successfully',
					], 200);

		}

		/* Reset Password Customer
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	email: string (email) (required)
		*/
		public function resetPasswordCustomer(Request $request)
		{
					$errorMessage = '';

					if(!$request->has('email'))
					{
							if($errorMessage != '') $errorMessage .= ', ';
							$errorMessage .= 'Email is required.';
					}
					else
					{
							if(!filter_var($request->email, FILTER_VALIDATE_EMAIL))
							{
									if($errorMessage != '') $errorMessage .= ', ';
									$errorMessage .= 'Email is not valid format.';
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

					$email = $request->email;
					$credentials = ['email' => $email];

					$response = Password::sendResetLink($credentials);

					/*
	        switch ($response) {
	            case Password::RESET_LINK_SENT:
	                return redirect()->back()->with('status', trans($response));
	            case Password::INVALID_USER:
	                return redirect()->back()->withErrors(['email' => trans($response)]);
	        }
					*/

					return Response::json([
							'success' => true,
							'message' => 'If you entered a correct email address, you will receive an email shortly.',
							'response' => $response
					], 200);

		}

		/* Add Credit Customer
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	id: integer (required)
				- credit: integer (required)
		*/
		public function addCreditCustomer(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if(!$request->has('credit'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Credit is required.';
				}

				if($request->has('credit') && !is_numeric($request->credit))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Credit is not valid format.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				$credit = $request->credit;
				if($this->api_type == 'sandbox')
					$user = User::where('id', $id)
																->where('api_type', $this->api_type)
																->first();
				else
					$user = User::where('id', $id)
																->where(function($query) {
										                $query->whereNull('api_type')
										                      ->orWhere('api_type', $this->api_type);
										            })
																->first();

				if(!$user)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'User ID: ' . $id . ' not found.'
																	],
																	401);
				}

				$credit = MiscStorage::where('name', 'account-credit')->where('user_id', $user->id)->first();

				if(!$credit) {
					$credit = new MiscStorage;
					$value = 0;
				} else {
					$value = $credit->value + $request->credit;
				}

				$credit->name = "account-credit";
				$credit->user_id = $user->id;
				$credit->value = $value;
				$credit->save();

				return Response::json([
						'success' => true,
						'message' => 'Credit User ID: ' . $user->id . ' added successfully',
						'credit' => $credit->value
				], 200);

		}

		/* Get Credit Customer
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	id: integer (required)
		*/
		public function getCreditCustomer(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				if($this->api_type == 'sandbox')
					$user = User::where('id', $id)
																->where('api_type', $this->api_type)
																->first();
				else
					$user = User::where('id', $id)
																->where(function($query) {
										                $query->whereNull('api_type')
										                      ->orWhere('api_type', $this->api_type);
										            })
																->first();

				if(!$user)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'User ID: ' . $id . ' not found.'
																	],
																	401);
				}

				$credit = MiscStorage::where('name', 'account-credit')->where('user_id', $user->id)->first();

				if(!$credit) {
					$credit = new MiscStorage;
					$value = 0;
				} else {
					$value = $credit->value + $request->credit;
				}

				return Response::json([
						'success' => true,
						'user_id' => $user->id,
						'credit' => $value
				], 200);

		}

		/* Get Credit Customer
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	id: integer (required)
		*/
		public function deletePayMethodCustomer(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				if($this->api_type == 'sandbox')
					$user = User::where('id', $id)
																->where('api_type', $this->api_type)
																->first();
				else
					$user = User::where('id', $id)
																->where(function($query) {
																		$query->whereNull('api_type')
																					->orWhere('api_type', $this->api_type);
																})
																->first();

				if(!$user)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'User ID: ' . $id . ' not found.'
																	],
																	401);
				}

				$user->stripeId = '';
				$user->save();

				SavedPaymentMethods::where('user_id', $user->id)->delete();

				return Response::json([
						'success' => true,
						'user_id' => $user->id,
						'message' => 'Pay Method deleted successfully.'
				], 200);
		}

		/* Get Credit Customer
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				-	id: integer (required)
 				-	note: string (required)
		*/
		public function addNoteCustomer(Request $request)
		{
				$errorMessage = '';

				if(!$request->has('id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'ID is required.';
				}

				if(!$request->has('note'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Note is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$id = $request->id;
				if($this->api_type == 'sandbox')
					$user = User::where('id', $id)
																->where('api_type', $this->api_type)
																->first();
				else
					$user = User::where('id', $id)
																->where(function($query) {
																		$query->whereNull('api_type')
																					->orWhere('api_type', $this->api_type);
																})
																->first();

				if(!$user)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'User ID: ' . $id . ' not found.'
																	],
																	401);
				}

				$notes = MiscStorage::create([
					'name' => "customer-notes-{$user->id}",
					'user_id' => $this->user->id,
					'value' => $request->note,
				]);

				return Response::json([
						'success' => true,
						'user_id' => $user->id,
						'message' => 'Note added successfully.'
				], 200);
		}
}
