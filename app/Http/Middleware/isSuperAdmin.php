<?php

namespace App\Http\Middleware;

use Closure;

class isSuperAdmin
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
        if (! Config('app.site')->super) {
            return abort(404);
        }

        return $next($request);
    }
}
