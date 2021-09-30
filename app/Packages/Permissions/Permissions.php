<?php

namespace App\Packages\Permissions;

// The code here is in Provider.php
// This is just a facade.

use \Illuminate\Support\Facades\Facade;

class Permissions extends Facade
{
	public static $exception = PermissionException::class;

	protected static function getFacadeAccessor()
	{
		return 'permissions';
	}
}

class PermissionException extends \Exception {}