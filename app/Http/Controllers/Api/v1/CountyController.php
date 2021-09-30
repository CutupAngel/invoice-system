<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Countries;
use App\Counties;
use App\User;

use Response;
use Illuminate\Http\Request;

class CountyController extends Controller
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

		/* Lists Counties By Country
			 Params
			 Header:
			 	- token: string (required)

			 Body:
 				- country_id: integer (required)
		*/
		public function listsCountiesByCountry(Request $request)
		{
				$errorMessage = '';
				if(!$request->country_id)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Country ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$country_id = $request->country_id;

				$counties = Counties::where('country_id', $country_id)->orderBy('id')->get();

				if(!$counties)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Country ID: ' . $id . ' not found.'
																	],
																	401);
				}

				return Response::json([
						'success' => true,
						'counties' => $counties
				], 200);
		}
}
