<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Countries;
use App\User;

use Response;
use Illuminate\Http\Request;

class CountryController extends Controller
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

		/* Lists Country
			 Params
			 Header:
			 	- token: string (required)
		*/
		public function listsCountry(Request $request)
		{
				$countries = Countries::orderBy('id')->get();

				return Response::json([
						'success' => true,
						'countries' => $countries
				], 200);
		}

		/* Get Country
			 Params
			 Header:
			 	- token: string (required)

			 Body:
				- id: integer (required)
		*/
		public function getCountry(Request $request)
		{
				$errorMessage = '';
				if(!$request->id)
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

				$country = Countries::find($id);

				if(!$country)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Country ID: ' . $id . ' not found.'
																	],
																	401);
				}

				return Response::json([
						'success' => true,
						'country' => $country
				], 200);
		}
}
