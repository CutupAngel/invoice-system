<?php

namespace App\Http\Middleware;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Site;
use Closure;
use Session;

class DatabaseSelector
{
	public function handle($request, Closure $next)
	{
		try {
			$site = Site::findOrFail(preg_replace('/(https|http):\/\//', '', url('/')));
			$site->setDatabase();
		} catch (ModelNotFoundException $e) {
			return redirect('https://billingserv.com');
		}

		return $next($request);
	}
}
