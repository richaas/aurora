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
		$ne = explode(":", base64_decode($nonce));

		if (count($ne) !== 2)
			return false;

		if ($ne[1] !== md5($ne[0] . ":" . $this->secret))
			return false;

		if ($ne[0] < time())
			return false;

		return true;
	}


        private function unauthorized($stale=false)
	{
		$ts = time() + $this->nonceExpires;

		header(sprintf("WWW-Authenticate: Digest realm=\"%s\", nonce=\"%s\", qop=\"auth\"%s",
			       $this->realm,
			       base64_encode($ts . ":" . md5($ts . ":" . $this->secret)),
			       $stale ? ", stale=true" : ""));

		throw new \Exception("unauthorized", 401);
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
