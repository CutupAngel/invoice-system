<?php

namespace App\Packages\Integrations;

// The code here is in Provider.php
// This is just a facade.

use \Illuminate\Support\Facades\Facade;

class Integrations extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'integrations';
	}
}
