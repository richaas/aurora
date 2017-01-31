<?php

namespace aurora\http\auth;


class digest
{
	private $_secret;
	private $_realm;
	private $_nonceExpires;
	private $_stale = false;
	public $username;
	public $realm;
	public $nonce;
	public $uri;
	public $response;
	public $qop;
	public $nc;
	public $cnonce;


	private function checkNonce()
	{
		$ne = explode(":", base64_decode($this->nonce));

		if (count($ne) !== 2)
			return false;

		if ($ne[1] !== md5($ne[0] . ":" . $this->_secret))
			return false;

		if ($ne[0] < time())
			return false;

		return true;
	}


	public function __construct($secret, $realm, $nonceExpires=300)
	{
		$this->_secret = $secret;
		$this->_realm  = $realm;
		$this->_nonceExpires = $nonceExpires;
	}


	public function decode()
	{
		if (!isset($_SERVER["PHP_AUTH_DIGEST"]))
			return false;

		if (!preg_match_all('/([a-z]+)\s*=\s*(?:"((?:[^"\\\\]|\\\\.)*)"|([^\s,]*))/si',
				    $_SERVER["PHP_AUTH_DIGEST"], $matches, PREG_SET_ORDER))
			return false;

		foreach ($matches as $ent) {
			$key = strtolower($ent[1]);
			$this->$key = isset($ent[3]) ? $ent[3] : $ent[2];
		}

		if ($this->realm != $this->_realm)
			return false;

		if (!$this->checkNonce()) {
			$this->_stale = true;
			return false;
		}

		return true;
	}


	public function validate($ha1)
	{
		$ha2 = md5($_SERVER["REQUEST_METHOD"] . ":" . $this->uri);

		if (isset($this->qop))
			$digest = md5("$ha1:$this->nonce:$this->nc:$this->cnonce:$this->qop:$ha2");
		else
			$digest = md5("$ha1:$this->nonce:$ha2");

		return ($this->response === $digest);
	}


	public function header()
	{
		$ts = time() + $this->_nonceExpires;

		return sprintf("Digest realm=\"%s\", nonce=\"%s\", qop=\"auth\"%s",
			       $this->_realm,
			       base64_encode($ts . ":" . md5($ts . ":" . $this->_secret)),
			       $this->_stale ? ", stale=true" : "");
	}
}
