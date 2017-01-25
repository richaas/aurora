<?php

namespace aurora\util;


class base64url
{
	public static function encode($data)
	{
		return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
	}


	public static function decode($data)
	{
		$pad = strlen($data) % 4;
		$pad = $pad ? 4 - $pad : 0;

		while ($pad--)
			$data .= "=";

		return base64_decode(strtr($data, "-_", "+/"));
	}
}
