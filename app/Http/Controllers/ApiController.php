<?php

namespace App\Http\Controllers;
use App\Repositories\DeviceRepository;
use App\Transformers\ApiJsonResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Swagger(
 *     basePath="/api",
 *     host="127.0.0.1:8080",
 *     schemes={"http"},
 *     produces={"application/json"},
 *          @SWG\Info(
 *              title="Laravel REST API backend for trimming video",
 *              version="0.1",
 *              description="If you need to trim some videos then use this rest api.",
 *              @SWG\Contact(name="Dima Gunchenko",email="terkin.web@gmail.com"),
 *              @SWG\License(name="Unlicense")
 *          ),
 * )
 */
class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiJsonResponse;

    const BAD_DEVICE_TOKEN      = 1000;
    const MISSING_PARAM         = 4000;
    const NOT_OWNER             = 3000;
    const INVALID_STATUS_CHANGE = 5000;
    const ROUTE_NOT_FOUND       = 4001;

    protected $deviceToken;

    public function __construct(Request $request)
    {
    	$this->deviceToken = $request->header('deviceToken');
    }

	/**
	 * @SWG\Get(
	 *     path="/request-token",
	 *     summary="Register device and returns deviceToken",
	 *     @SWG\Response(
	 *          response=200,
	 *          description="Generate and returns new device token",
	 *          examples={
	 *              "application/json": {
	 *                  "statusCode"=200,
	 *                  "statusMessage"="OK",
	 *                  "data"={
	 *                      "deviceToken"="$2y$10$9PCR/OVAPeilqB4noJGDZOQXZtqnLA0m/OTnCoBbDGML4Ds8lzvUW",
	 *                  }
	 *              }
	 *          }
	 *      ),
	 * ),
	 */
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
