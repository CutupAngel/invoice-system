<?php

namespace App\Http\Middleware;

use Closure;

class isStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user()->isStaff() && !$request->user()->isClient() && !$request->user()->isAdmin()) {
            return abort(404);
        }
		
        return $next($request);
    }
}
