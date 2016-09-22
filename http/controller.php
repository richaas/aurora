<?php

namespace aurora\http;


class controller
{
	private static function checkMethod($class, $method, $argc)
	{
		if (!method_exists($class, $method))
			return false;

		$rm = new \ReflectionMethod($class, $method);

		if (!$rm->isPublic())
			return false;

		if ($argc < $rm->getNumberOfRequiredParameters() ||
		    $argc > $rm->getNumberOfParameters())
			return false;

		return true;
	}


	private static function reply($class, $method, $args)
	{
		$resp = call_user_func_array(array(new $class, $method), $args);
		if (!isset($resp))
			$resp = new response("", 204, array("Content-Type:"));

		header("HTTP/1.1 " . $resp->status);

		foreach ($resp->headers as $value)
			header($value);

		echo $resp->content;
	}


	private static function expand(&$val, &$args, $index)
	{
		$val .= str_replace(array("-", "/"), array("_", "\\"), $args[$index]);

		unset($args[$index]);
	}


	public static function request($routes)
	{
		$req = new request();

		foreach ($routes as $rt) {

                        if (!preg_match($rt["path"], $req->path, $args))
				continue;

			$class   = $rt["ctrl"];
			$method  = $req->method;
			$args[0] = $req;

			if (isset($rt["class"]))
				self::expand($class, $args, $rt["class"]);

			if (isset($rt["method"]))
				self::expand($method, $args, $rt["method"]);

			if (!self::checkMethod($class, $method, count($args)))
				continue;

			return self::reply($class, $method, $args);
		}

		throw new \Exception("not found", 404);
	}
}
