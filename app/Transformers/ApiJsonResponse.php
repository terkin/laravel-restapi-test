<?php
namespace App\Transformers;

trait ApiJsonResponse {
	public function sendResponse(array $data, $statusCode = 200, $errorCode = 0) {
		$response = [
			'statusCode' => $statusCode,
			'statusMessage' => ($statusCode === 200 ) ? 'OK' : 'ERROR',
		];

		if($errorCode !== 0) {
			$response['errorCode'] = $errorCode;
			$response['errors'] = $data;
		} else {
			$response['data'] = $data;
		}

		return response()->json($response, $statusCode);
	}
}