<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class DeviceAuth
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
	    $loginToken = $request->header('deviceToken');
	    if(null === $loginToken) {
		    throw new UnauthorizedHttpException('DeviceToken', 'Please provide deviceToken.');
	    }
        return $next($request);
    }
}
