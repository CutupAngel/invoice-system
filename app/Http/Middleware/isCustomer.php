<?php

namespace App\Http\Middleware;

use Closure;

class isCustomer
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
        if (!$request->user()->isCustomer()) {
            return abort(404);
        }

        return $next($request);
    }
}
