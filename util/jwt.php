<?php

namespace aurora\util;

use Exception;


class jwt
{
	private $alg;
	private $jwt;


	public static function encode($payload, $key, $alg="HS256")
	{
		$hdr = base64url::encode(json_encode(array("alg"=>$alg, "typ"=>"JWT")));
		$pld = base64url::encode(json_encode($payload));
		$pkt = $hdr . "." . $pld;

		switch ($alg) {

		case "HS256":
			$sig = base64url::encode(hash_hmac("sha256", $pkt, $key, true));
			break;

		default:
			throw new Exception("unsupported jwt algorithm: $alg");
		}

		return $pkt . "." . $sig;
	}


	public function decode($token)
	{
		$jwt = explode(".", $token);
		if (count($jwt) !== 3)
			return NULL;

		$hdr = json_decode(base64url::decode($jwt[0]));
		if ($hdr === NULL || !isset($hdr->alg))
			return NULL;

		$pld = json_decode(base64url::decode($jwt[1]));
		if ($pld === NULL)
			return NULL;

		$this->alg = $hdr->alg;
		$this->jwt = $jwt;

		return $pld;
	}


	public function validate($key)
	{
		$pkt = $this->jwt[0] . "." . $this->jwt[1];

		switch ($this->alg) {

		case "HS256":
			$sig = base64url::encode(hash_hmac("sha256", $pkt, $key, true));
			break;

		default:
			return false;
		}

		return ($this->jwt[2] === $sig);
	}
}
