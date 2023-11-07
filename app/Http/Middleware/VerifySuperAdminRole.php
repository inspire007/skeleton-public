<?php

namespace App\Http\Middleware;

use Closure;

class VerifySuperAdminRole
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
		$abort = 1;
		if (auth()->check() && auth()->user()->user_role) {
            if( auth()->user()->user_role >= 50 )$abort = 0;
        }
		
		if($abort)abort(404);
		
        return $next($request);
    }
}
