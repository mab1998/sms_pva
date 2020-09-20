<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {
        if (!Auth::guard($guard)->check()) {
            return redirect('admin')->with([
                'message'=> language_data('Invalid Access'),
                'message_important'=>true
            ]);
        }elseif (Auth::guard($guard)->check()){
            if (app_config('purchase_key')==''){
                return redirect('verify-purchase-code');
            }
        }
        return $next($request);
    }
}
