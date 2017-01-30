<?php

namespace aurora\util;

use Exception;


class rand
{
	private static $alphanum = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	private static $alphanumLength = 62;


	public static function string($length)
	{
		$random = self::bytes($length);
		$string = "";

		for ($idx=0; $idx<$length; $idx++)
			$string .= self::$alphanum[ord($random[$idx]) % self::$alphanumLength];

		return $string;
	}


	public static function bytes($length)
	{
		$random = openssl_random_pseudo_bytes($length);
		if ($random === false)
			throw new Exception("unable to generate random bytes");

		return $random;
	}
}
