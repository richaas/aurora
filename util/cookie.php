<?php

namespace aurora\util;


class cookie
{
	public static function set($name, $value="", $lifetime=0, $path="", $samesite="Lax",
				   $domain="", $secure=true, $httponly=true)
	{
		return setcookie($name, $value, ["expires"  => $lifetime ? time() + $lifetime : 0,
						 "path"     => $path,
						 "samesite" => $samesite,
						 "domain"   => $domain,
						 "secure"   => $secure,
						 "httponly" => $httponly]);
	}
}
