<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class Impersonate
{
	public function handle($request, Closure $next)
	{
		if ($request->session()->has('impersonate')) {
			Auth::onceUsingId($request->session()->get('impersonate'));
		}

		$response = $next($request);

		return $response;
	}
}
