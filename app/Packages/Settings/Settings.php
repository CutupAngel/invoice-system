<?php

namespace App\Packages\Settings;

// The code here is in Provider.php
// This is just a facade.

use \Illuminate\Support\Facades\Facade;

class Settings extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'settings';
	}
}
