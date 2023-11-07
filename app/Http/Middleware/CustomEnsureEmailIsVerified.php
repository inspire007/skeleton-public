<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

class CustomEnsureEmailIsVerified extends EnsureEmailIsVerified
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
		if(!config('site.email_verify'))return $next($request);
        return parent::handle($request, $next);
    }
}
