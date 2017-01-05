<?php

namespace aurora\util;


class cidr
{
	public static function match($ip, $cidr)
	{
		list($subnet, $mask) = explode("/", $cidr);

		return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
	}
}
