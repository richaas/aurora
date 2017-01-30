<?php

namespace aurora\crypto;

use aurora\util\rand;
use Exception;


class aes
{
	const method = "aes-256-ctr";
	const ivsize = 16;


	public static function encrypt($data, $key)
	{
		$iv = rand::bytes(self::ivsize);

		$res = openssl_encrypt($data, self::method,
				       hash("sha256", $key),
				       OPENSSL_RAW_DATA, $iv);
		if ($res === false)
			throw new Exception("aes: unable to encrypt data");

		return base64_encode($res . $iv);
	}


	public static function decrypt($data, $key)
	{
		$raw = base64_decode($data);

		$iv = substr($raw, -self::ivsize);

		return openssl_decrypt(substr($raw, 0, -self::ivsize), self::method,
				       hash("sha256", $key),
				       OPENSSL_RAW_DATA, $iv);
	}
}
