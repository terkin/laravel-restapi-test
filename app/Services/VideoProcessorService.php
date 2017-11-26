<?php
namespace App\Services;
use \App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use League\Flysystem\FileNotFoundException;

class VideoProcessorService implements VideoProcessorInterface
{
	protected $videoPath;

	public function process(string $video) : VideoProcessorInterface
	{
		echo $video;
		if(!file_exists($video)) {
			throw new FileNotFoundException($video);
		}
		$this->videoPath = $video;
		return $this;
	}

	public function trim(int $start = null, int $end = null) : int
	{
		echo "trimming {$start}:{$end} from " . $this->videoPath;
		$duration = rand(20,120);
		sleep($duration);
		return $duration;
	}

}

interface VideoProcessorInterface {
	public function process(string $video) : VideoProcessorInterface;
	public function trim(int $start, int $end) : int;
}