<?php
namespace App\Transformers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class VideoTransformer implements ApiResponseTransformer
{

	public function transform(Model $video) : array {
		return [
			'id'       => (string) $video->_id,
			'status'   => (string) $video->status,
			'url'      => Storage::url($video->fileName), //todo use s3 or change logic to generate proper url
			'updated'  => $video->updated_at->toDateTimeString(),
		];
	}

}

interface ApiResponseTransformer {
	public function transform(Model $data) : array;

}