<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\VideoProcessorService;
use App\Models\Video;
use App\Repositories\VideoRepository;


class EnqueueVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $tries = 5;

    private $id;


    public function __construct($id)
    {
        $this->id = $id;
    }


    public function handle(VideoProcessorService $service, VideoRepository $repository)
    {
    	/** @var Video $model */
		$model = $repository->getVideoById($this->id);
	    $model->process();
		if($duration = $service->process($model->fileName)->trim($model->start, $model->end)) {
			$model->duration = $duration;
			$model->done();
		} else {
			$model->fail();
		}
	    $model->save();
    }
}
