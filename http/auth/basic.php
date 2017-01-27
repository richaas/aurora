<?php

namespace aurora\http\auth;


class basic
{
	private $realm;
	public $username;
	public $password;


	public function __construct($realm)
	{
		$this->realm = $realm;
	}


	public function decode()
	{
		if (!isset($_SERVER["PHP_AUTH_USER"]))
			return false;

		$this->username = $_SERVER["PHP_AUTH_USER"];
		$this->password = $_SERVER["PHP_AUTH_PW"];

		return true;
	}


	public function header()
	{
		return sprintf("Basic realm=\"%s\"", $this->realm);
	}
}
