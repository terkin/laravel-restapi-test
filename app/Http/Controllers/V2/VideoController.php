<?php

namespace App\Http\Controllers\V2;

use App\Jobs\EnqueueVideo;
use App\Http\Controllers\ApiController;
use App\Models\Video;
use App\Models\Device;
use Finite\StateMachine\StateMachine;
use Finite\StateMachine\StateMachineInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends ApiController
{

	public function version()
	{
		return $this->sendResponse(['version' => '2']);
	}
}
