<?php

namespace App\Repositories;

use Illuminate\Container\Container as App;
use Jenssegers\Mongodb\Eloquent\Model;

abstract class BaseRepository extends Model {
	/**
	 * @var App
	 */
	private $app;

	protected $model;

	public function __construct(App $app)
	{
		$this->app = $app;
		$this->makeModel();
	}

	abstract public function model() : string;

	/**
	 * @return \Illuminate\Database\Eloquent\Builder
	 * @throws \Exception
	 */
	public function makeModel()
	{
		return $this->setModel($this->model());
	}


	public function setModel($eloquentModel)
	{
		$this->model = $this->app->make($eloquentModel);
		if (!$this->model instanceof Model)
			throw new \Exception("Class {$this->model} must be an instance of Illuminate\\Database\\Eloquent\\Model");
		return $this->model;
	}

	public function getWhere(array $fields = [], ...$condition) {
		$model = $this->model;
		return $model::where(...$condition)->get($fields);
	}

	public function create(array $array = []) : Model {
		foreach ($array as $field => $value) {
			$this->model->{$field} = $value;
		}

		$this->model->save();
		return $this->model;
	}
}