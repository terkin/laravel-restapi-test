<?php
namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Acacha\Stateful\Traits\StatefulTrait;
use Acacha\Stateful\Contracts\Stateful;
use Illuminate\Support\MessageBag;

/**
 * @property string $fileName
 * @property string $deviceToken
 * @property string $status
 * @method process - performs status field transaction
 * @method done - performs status field transaction
 * @method fail - performs status field transaction
 * @method failed - performs check if status field = fail
 * @method restart - performs status field transaction
 */

class Video extends Eloquent implements Stateful
{
	use StatefulTrait;

	const STATE = 'status';

	const STATUS_SCHEDULED = 'scheduled';
	const STATUS_PROCESSING = 'processing';
	const STATUS_FAIL = 'failed';
	const STATUS_DONE = 'complete';

	protected $collection = 'video_collection';
	protected $guarded = []; //this cost me around 1 hour


	protected $defaults = array(
		'status' => self::STATUS_SCHEDULED,
	);

	/**
	 * Transaction States
	 *
	 * @var array
	 */
	protected $states = [
		'scheduled' => ['initial' => true],
		'processing',
		'failed',
		'complete' => ['final' => true]
	];

	/**
	 * Transaction State Transitions
	 *
	 * @var array
	 */
	protected $transitions = [
		'restart' => [
			'from' => ['failed'],
			'to'   => 'scheduled'
		],
		'process' => [
			'from' => ['scheduled'],
			'to' => 'processing'
		],
		'done' => [
			'from' => 'processing',
			'to' => 'complete'
		],
		'fail' => [
			'from' => 'processing',
			'to' => 'failed'
		],
	];

	public function __construct(array $attributes = array())
	{
		$this->setRawAttributes($this->defaults, true);
		$this->errorMessages = new MessageBag();
		parent::__construct($attributes);
	}

	public function getDeviceToken() : string {
		return $this->deviceToken;
	}
}