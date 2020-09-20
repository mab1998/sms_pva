<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{

    protected $session;
    protected $timeout=900;
    public function __construct(Store $session){
        $this->session=$session;
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$this->session->has('lastActivityTime'))
            $this->session->put('lastActivityTime',time());
        elseif(time() - $this->session->get('lastActivityTime') > $this->getTimeOut()){
            $this->session->forget('lastActivityTime');

            if (Auth::guard('client')->check()){
                Auth::guard('client')->logout();
                return redirect('/')->withErrors(['You had not activity in 15 minutes']);
            }

            Auth::logout();
            return redirect('admin')->withErrors(['You had not activity in 15 minutes']);
        }
        $this->session->put('lastActivityTime',time());
        return $next($request);
    }

    protected function getTimeOut()
    {
        return (env('TIMEOUT')) ?: $this->timeout;
    }
}
