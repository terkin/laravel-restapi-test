<?php
namespace App\Repositories;


use App\Models\Device;

class DeviceRepository extends BaseRepository
{
	public function model() : string {
		return 'App\Models\Device';
	}

	public function createToken() : Device {
		$device = $this->create(['deviceToken' => password_hash(bin2hex(random_bytes(5)), PASSWORD_BCRYPT)]);
		return $device;
	}

	public function findDeviceByToken($token) : ?Device {
		return $this->getWhere([], 'deviceToken', '=', $token)->first();

	}
}