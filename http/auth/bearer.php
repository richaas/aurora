<?php

namespace aurora\http\auth;


class bearer
{
	private $realm;
	private $scope;
	private $error;
	private $error_description;
	private $error_uri;
	public $token;


	public function __construct($realm, $scope=NULL)
	{
		$this->realm = $realm;
		$this->scope = $scope;
	}


	public function decode()
	{
		if (!isset($_SERVER["HTTP_AUTHORIZATION"]))
			return false;

		if (!preg_match("/^Bearer\s+(.+)$/si", $_SERVER["HTTP_AUTHORIZATION"], $matches))
			return false;

		$this->token = $matches[1];

		return true;
	}


	public function setError($error, $description=NULL, $uri=NULL)
	{
		$this->error = $error;
		$this->error_description = $description;
		$this->error_uri = $uri;
	}


	public function header()
	{
		$hdr = "Bearer";

		foreach ($this as $key => $value) {

			if (!isset($this->$key) || $key === "token")
				continue;

			$hdr .= sprintf(" %s=\"%s\",", $key, $value);
		}

		return rtrim($hdr, ",");
	}
}
