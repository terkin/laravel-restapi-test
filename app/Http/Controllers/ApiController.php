<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Repositories\DeviceRepository;
use App\Transformers\ApiJsonResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiJsonResponse;

    const BAD_DEVICE_TOKEN      = 1000;
    const MISSING_PARAM         = 4000;
    const NOT_OWNER             = 3000;
    const INVALID_STATUS_CHANGE = 5000;
    const ROUTE_NOT_FOUND       = 4001;

    protected $deviceToken;
    protected $stateMachine;

    public function __construct(Request $request)
    {
    	$this->deviceToken = $request->header('deviceToken');
    }

	public function requestToken(DeviceRepository $repo) {
		$device = $repo->createToken();
		return $this->sendResponse(['deviceToken' => $device->getToken()]);
    }

    protected function validateOwnership(Model $model) {
	    $validator = Validator::make((array) $model,  ['deviceToken' => $this->deviceToken ]);

	    if ($validator->fails()) {
		    return $this->sendResponse(['You dont have permissions'], 403, self::NOT_OWNER);
	    }
    }
}
