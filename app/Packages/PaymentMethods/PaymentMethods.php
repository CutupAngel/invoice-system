<?php

namespace App\Packages\PaymentMethods;

// The code here is in Provider.php
// This is just a facade.

use \Illuminate\Support\Facades\Facade;

class PaymentMethods extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'paymentmethods';
	}
}
