<?php

namespace App\Http\Middleware;

use Closure;

class CheckDemo
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
        if (env('APP_STAGE') == 'Demo'){
            return redirect('admin')->with([
                'message'=> language_data('Invalid Access'),
                'message_important'=>true
            ]);
        }
        return $next($request);
    }
}
