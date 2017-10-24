<?php

namespace aurora\util;

use Exception;


class jwt
{
	private $signature;
	private $packet;
	public $header;
	public $payload;


	public static function encode($payload, $key, $alg="HS256")
	{
		$hdr = base64url::encode(json_encode(array("alg"=>$alg, "typ"=>"JWT")));
		$pld = base64url::encode(json_encode($payload));
		$pkt = $hdr . "." . $pld;

		switch ($alg) {

		case "HS256":
			$sig = hash_hmac("sha256", $pkt, $key, true);
			if ($sig === false)
				throw new Exception(error_get_last()["message"]);
			break;

		case "RS256":
			if (!openssl_sign($pkt, $sig, $key, "sha256"))
				throw new Exception(error_get_last()["message"]);
			break;

		default:
			throw new Exception("unsupported jwt algorithm: " . $alg);
		}

		return $pkt . "." . base64url::encode($sig);
	}


	public static function decode($token, $key)
	{
		$jwt = new jwt($token);

		if (!$jwt->validate($key))
			throw new Exception("invalid credentials", 400);

		return $jwt->payload;
	}


	public function __construct($token)
	{
		$jwt = explode(".", $token);
		if (count($jwt) !== 3)
			throw new Exception("invalid token", 400);

		$this->header = json_decode(base64url::decode($jwt[0]));
		if ($this->header === NULL || !isset($this->header->alg))
			throw new Exception("invalid token header", 400);

		$this->payload = json_decode(base64url::decode($jwt[1]));
		if ($this->payload === NULL)
			throw new Exception("invalid token payload", 400);

		$this->signature = base64url::decode($jwt[2]);
		$this->packet    = $jwt[0] . "." . $jwt[1];
	}


	public function validate($key)
	{
		switch ($this->header->alg) {

		case "HS256":
			$sig = hash_hmac("sha256", $this->packet, $key, true);
			if ($sig === false)
				throw new Exception(error_get_last()["message"]);

			return $sig === $this->signature;

		case "RS256":
			$res = openssl_verify($this->packet, $this->signature, $key, "sha256");
			if ($res === false)
				throw new Exception(error_get_last()["message"]);

			return $res === 1;

		default:
			throw new Exception("unsupported jwt algorithm: " . $this->header->alg);
		}
	}
}
