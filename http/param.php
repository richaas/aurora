<?php

namespace aurora\http;

use Exception;


class param
{
	public $param;


	public function __construct($param)
	{
		$this->param = (object)$param;
	}


	public function get($key, $placeholder=NULL)
	{
		if (property_exists($this->param, $key))
			return $this->param->$key;
		else if (func_num_args() < 2)
			throw new Exception("parameter $key must be provided", 400);
		else
			return $placeholder;
	}


	public function getParam($key, &$value=NULL)
	{
		if (!property_exists($this->param, $key))
			return false;

		$value = $this->param->$key;

		return true;
	}
}
