<?php

namespace App\Packages\Permissions;

use App;
use Auth;
use Settings;

use App\User;

class Provider
{
	private static $overrideProfiles = [
		User::ADMIN,
		User::CLIENT
	];

	private static $cached = [];
	private static $override = null;

	public function __construct()
	{
		if (empty(self::$cached)) {
			self::$cached = Settings::get('permission');
		}

		// If we're running php artistan commands we need to have full permissions.
		// Otherwise if they're not logged in they don't have any permissions.
		if (App::runningInConsole() || (Auth::check() && in_array(Auth::User()->account_type, self::$overrideProfiles))) {
			self::$override = true;
		} else {
			self::$override = false;
		}
	}

	public function has($permission)
	{
		try {
			if (self::$override) {
				return true;
			} elseif (isset(self::$cached[$permission]) && self::$cached[$permission] == 'Y') {
				return true;
			}
		} finally {
		}

		return false;
	}
}
