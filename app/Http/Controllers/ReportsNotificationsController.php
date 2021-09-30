<?php

use App\Controller;
use App\Login_History;
use App\Providers\NotificationServiceProvider;

// Invalid Logins
$user = Auth::User();
$attempts = Login_History::where('username', $user->username)
	->where('created_at', '>=', $user->lastLogin())
	->where('failed', true)
	->count();

if ($attempts !== 0) {
	NotificationServiceProvider::add(
		$attempts.' failed login '. str_plural('attempt', $attempts). ' since last login.',
		'#',
		'fa fa-users text-red'
	);
}
