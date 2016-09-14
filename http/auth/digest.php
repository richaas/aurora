<?php

namespace aurora\http\auth;


class digest
{
	private $secret;
	private $realm;
	private $nonceExpires;


	private function decode($hdr)
	{
		$params = array();

		$comp = explode(",", $hdr);

		foreach($comp as $val) {

			$ent = explode("=", $val, 2);

			$key = trim($ent[0]);

			$params[$key] = trim($ent[1], "\"");
		}

		return (object)$params;
	}


	private function checkNonce($nonce)
	{
		$nkey = substr($nonce, 0, 32);
		$ts   = hexdec(substr($nonce, 32));
		$ckey = md5($this->secret . $ts);

		if ($nkey !== $ckey)
			return false;

		$age = time() - $ts;

		if ($age < 0 || $age > $this->nonceExpires)
			return false;

		return true;
	}


        private function unauthorized($stale=false)
	{
		$ts = time();

		header(sprintf("WWW-Authenticate: Digest realm=\"%s\", nonce=\"%s\", qop=\"auth\"%s",
			       $this->realm,
			       md5($this->secret . $ts) . dechex($ts),
			       $stale ? ", stale=true" : ""));

		throw new \Exception("Unauthorized", 401);
	}


	public function __construct($secret, $realm, $nonceExpires=300)
	{
		$this->secret = $secret;
		$this->realm  = $realm;
		$this->nonceExpires = $nonceExpires;
	}


	public function authenticate($func)
	{
		if (!isset($_SERVER["PHP_AUTH_DIGEST"]))
			$this->unauthorized();

		$auth = $this->decode($_SERVER["PHP_AUTH_DIGEST"]);

		if ($auth->realm != $this->realm)
			$this->unauthorized();

		if (!$this->checkNonce($auth->nonce))
			$this->unauthorized(true);

		$res = $func($auth->username, $ha1);
		if (!$res)
			$this->unauthorized();

		$ha2 = md5($_SERVER["REQUEST_METHOD"] . ":" . $_SERVER["REQUEST_URI"]);

		if ($auth->qop)
			$digest = md5("$ha1:$auth->nonce:$auth->nc:$auth->cnonce:$auth->qop:$ha2");
		else
			$digest = md5("$ha1:$auth->nonce:$ha2");

		if ($auth->response !== $digest)
			$this->unauthorized();

		return $res;
	}
}
