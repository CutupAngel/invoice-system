<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class Api
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
        if ($token = $request->header('token')) {
            $user = User::where('sandbox_api_key', $token)
                ->orWhere('live_api_key', $token)
                ->first();

            if ($user) {
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });

                return $next($request);
            }

            return response()->json([
                'success' => false,
                'errors' => 'Invalid token.'
            ], 401);
        }

        return response()->json([
            'success' => false,
            'errors' => 'token is required.'
        ], 403);
    }
}
