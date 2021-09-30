<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class LogActivity
{
	private $bypass = [
		'api/migrate'
	];

	public function handle($request, Closure $next)
	{
		$response = $next($request);

		if ($request->route() !== null && !in_array(url()->current(), $this->bypass) && $request->user() !== null) {
			DB::insert(
				"INSERT INTO activity_log (
					login_id,
					route
				) VALUES (
					:login_id,
					:route
				)",
				[
					'login_id' => $request->user()->currentLogin->id,
					'route' => app_path() . print_r($request->route()->parameters(), true)
				]
			);
		}

		return $response;
	}
}
