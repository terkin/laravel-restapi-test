<?php

namespace App\Http\Controllers;

use App\Jobs\EnqueueVideo;
use App\Models\Video;
use App\Transformers\VideoTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Repositories\VideoRepository;

class VideoController extends ApiController {

	public function index(VideoRepository $videoRepo) {
		return $this->sendResponse(
			['items' => $videoRepo->getUserVideos($this->deviceToken)]
		);
	}

	public function status($id, VideoRepository $videoRepo) {
		$videoModel = $videoRepo->getVideoById($id);
		$this->validateOwnership($videoModel);

		return $this->sendResponse([
			'data' => (new VideoTransformer)->transform($videoModel)
		]);
	}

	public function restart($id, VideoRepository $videoRepo) {
		/** @var Video $videoModel */
		$videoModel = $videoRepo->getVideoById($id);
		$this->validateOwnership($videoModel);

		//only failed jobs can be restarted
		if($videoModel->failed()) {
			$videoModel->restart();

			$this->dispatch(
				new EnqueueVideo($videoModel->_id)
			);
		} else {
			return $this->sendResponse(
				['Only failed jobs can be restarted, current videoModel status is:' . $videoModel->status],
				400,
				self::INVALID_STATUS_CHANGE
			);
		}

		return $this->sendResponse([
			'data' => (new VideoTransformer)->transform($videoModel)
		]);
	}

	/**
	 * @SWG\Post(
	 *     path="/trim",
	 *     summary="Create new job to trim the video",
	 *     consumes={"multipart/form-data"},
	 *     @SWG\Parameter(
	 *         description="deviceToken",
	 *         in="header",
	 *         name="deviceToken",
	 *         required=true,
	 *         type="string"
	 *     ),
	 *     @SWG\Parameter(
	 *         description="video file to upload",
	 *         in="formData",
	 *         name="video",
	 *         required=true,
	 *         type="file"
	 *     ),
	 *     @SWG\Parameter(
	 *         description="seconds from the start to trim video",
	 *         in="formData",
	 *         name="start",
	 *         required=false,
	 *         type="integer"
	 *     ),
	 *     @SWG\Parameter(
	 *         description="seconds when stop trim video",
	 *         in="formData",
	 *         name="end",
	 *         required=false,
	 *         type="integer"
	 *     ),
	 *     @SWG\Response(
	 *          response=200,
	 *          description="video successfully enqued",
	 *     	    examples={
	 *              "application/json": {
	 *                  "statusCode"=200,
	 *                  "statusMessage"="OK",
	 *                  "data"={
	 *                      "status"="scheduled",
	 *                      "videoId"="5a1a8b2bfea65f00154056b3",
	 *                      "url"="http://site.com/storage/video/abs.mp4"
	 *                  }
	 *              }
	 *          }
	 *      ),
	 *     @SWG\Response(
	 *           response=400,
	 *           description="validation error",
	 *     	     examples={
	 *              "application/json": {
	 *                  "statusCode"=400,
	 *                  "statusMessage"="ERROR",
	 *                  "errorCode"=4000,
	 *                  "errors"={
	 *                      "start"={"The start field is required when none of start / end are present."},
	 *                      "end"={"The end field is required when none of start / end are present."}
	 *                  }
	 *              }
	 *          }
	 *      )
	 * ),
	 */
	public function trim(Request $request, VideoRepository $videoRepo) {
		$start = $request->get('start', 0);
		$end = $request->get('end');
		$videoFile = $request->file('video');

		$rules = [
			'start'    => 'required_without_all:start,end',
			'end'       => 'required_without_all:start,end',
			'video'     => 'required'
		];
		//TODO add video mime type validations to allow only supported files
		$validator = Validator::make($request->all(),  $rules);

		if ($validator->fails()) {
			return $this->sendResponse([$validator->errors()], 400, self::MISSING_PARAM);
		}

		$path = $this->saveFile($videoFile, $start, $end);

		/** @var \App\Models\Video $video */
		$video = $videoRepo->create([
			'fileName' => storage_path('app') .'/'. $path,
			'deviceToken' => $this->deviceToken,
			'start' => $start,
			'end' => $end
		]);

		$this->dispatch(
			new EnqueueVideo($video->_id)
		);

		return $this->sendResponse([
			'status' => $video->status,
			'videoId' => $video->getKey(),
			'url' => Storage::url($path) // use s3 driver to get proper url
		]);
	}

	/**
	 * @param UploadedFile $file
	 * @param int|null $start
	 * @param int|null $end
	 * @return array
	 */
	private function saveFile(UploadedFile $file, int $start = null, int $end = null) : string {
		$fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$fileName .= '-' . $start . '-' . $end . '.' . $file->getClientOriginalExtension();

		//TODO why its not in public folder?
		$path = $file->storePubliclyAs(
			'video/' . crc32($this->deviceToken),
			$fileName
		);
		return $path;
	}
}
