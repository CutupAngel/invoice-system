<?php

namespace App\Packages\Invoices;

// The code here is in Provider.php
// This is just a facade.

use \Illuminate\Support\Facades\Facade;

class Invoices extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'invoices';
	}
}
