<?php

namespace aurora\http\auth;


class basic
{
	private $realm;


	private function unauthorized()
	{
		header("WWW-Authenticate: Basic realm=\"$this->realm\"");
		throw new \Exception("unauthorized", 401);
	}


	public function __construct($realm)
	{
		$this->realm = $realm;
	}


	public function authenticate($func)
	{
		if (!isset($_SERVER["PHP_AUTH_USER"]))
			$this->unauthorized();

		$res = $func($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]);
		if (!$res)
			$this->unauthorized();

		return $res;
	}
}
