<?php

namespace App\Http\Middleware;

use Closure;

class CheckBanned
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
		if (auth()->check() && auth()->user()->banned_until && now()->lessThan(auth()->user()->banned_until)) {
            $banned_days = now()->diffInDays(auth()->user()->banned_until);
            auth()->logout();

            if ($banned_days > 14) {
                $message = __('Your account has been suspended. Please contact administrator.');
            } else {
                $message = sprintf(__('Your account has been suspended for %d %s. Please contact administrator.'), $banned_days, ($banned_days > 1 ? 'days' : 'day') );
            }

            return redirect()->route('login')->withMessage($message);
        }
		
        return $next($request);
    }
}
