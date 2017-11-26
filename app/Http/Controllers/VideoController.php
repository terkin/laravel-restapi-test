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

	public function trim(Request $request, VideoRepository $videoRepo) {
		$start = $request->get('start', 0);
		$end = $request->get('end');
		$videoFile = $request->file('video');

		$rules = [
			'start'    => 'required_without_all:start,end',
			'end'       => 'required_without_all:start,end',
			'video'     => 'required'
		];
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
