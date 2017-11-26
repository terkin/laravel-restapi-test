<?php
namespace App\Repositories;
use App\Models\Video;


class VideoRepository extends BaseRepository
{

	public function model() : string {
		return 'App\Models\Video';
	}

	public function getUserVideos(string $deviceToken) : array {
		$collections = $this->getWhere([], 'deviceToken', '=', $deviceToken);
		$return = [];
		/** @var Video $collection */
		foreach ($collections as $collection) {
			$video['id'] = $collection->_id;
			$video['status'] = $collection->status;
			$video['fileName'] = $collection->fileName;
			$video['start'] = $collection->start;
			$video['end'] = $collection->end;
			$video['duration'] = $collection->duration;
			$video['updated_at'] = $collection->updated_at;
			$return[] = $video;
		}
		return $return;
	}

	public function getVideoById($id) {
		return $this->model->find($id);
	}
}
