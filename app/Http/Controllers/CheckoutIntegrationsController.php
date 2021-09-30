<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Input;
use Response;
use Mail;

use Illuminate\Http\Request;
use App\User;


class CheckoutIntegrationsController extends Controller
{
	protected $integrations = [
		'sample' => 'Sample'
	];

	public function process($objPackage)
	{
		if (!empty($objPackage->integration) && array_key_exists($objPackage->integration,$this->integrations)) {
			return $this->{'process' . $this->integrations}();
		}
	}

	private function processSample()
	{
		//bla
		//maybe call a class in integrations subfolder? maybe not?
	}
}
