<?php

namespace App\Http\Middleware;
use App\Http\Controllers\ApiController;
use App\Transformers\ApiJsonResponse;
use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class DeviceAuth
{
	use ApiJsonResponse;

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
	    	return $this->sendResponse(['Please provide deviceToken.'], 401, ApiController::BAD_DEVICE_TOKEN);
	    }
	    $deviceRepo = resolve('App\Repositories\DeviceRepository');
	    $device = $deviceRepo->findDeviceByToken($loginToken);

	    if(null == $device || $device->getToken() !== $loginToken) {
		    return $this->sendResponse(['Please provide deviceToken.'], 401, ApiController::BAD_DEVICE_TOKEN);
	    }
        return $next($request);
    }
}
