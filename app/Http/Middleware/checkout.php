<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class checkout
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
        if ($key = $request->route('key')) {
            if ($data = Cache::get($key)) {
                $request->merge($data);
                return $next($request);
            }
        }

        return abort(403);
    }
}
