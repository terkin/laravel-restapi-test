<?php
namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Device extends Eloquent
{
	protected $collection = 'device_collection';
	protected $guarded = [];

	public function getToken() : string {
		return $this->deviceToken;
	}

}