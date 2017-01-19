<?php

namespace aurora\http;


class controller
{
	const arg  = "{arg}";
	const ctrl = "{ctrl}";
	const met  = "{met}";
	const root = "{root}";


	private static function checkMethod($class, $method, $argc)
	{
		try {
			$rm = new \ReflectionMethod($class, $method);
		}
		catch (\ReflectionException $ex) {
			return false;
		}

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

		$resp->sendContent();
	}


	public static function handleRequest($routes, $ctrlRoot="ctrl")
	{
		$req = new request();

		$nodes  = explode("/", substr($req->path, 1));
		$method = $req->method;
		$args   = array($req);
		$route  = $routes;
		$ctrl   = "";

		foreach ($nodes as $node) {

			$node = rawurldecode($node);

			if (isset($route[$node])) {
				$route = $route[$node];
			}
			else if (isset($route[self::arg])) {
				$route  = $route[self::arg];
				$args[] = $node;
			}
			else if (isset($route[self::ctrl])) {
				$route = $route[self::ctrl];
				$ctrl .= "\\" . preg_replace("/[^\w]/", "", $node);
			}
			else if (isset($route[self::met])) {
				$route   = $route[self::met];
				$method .= preg_replace("/[^\w]/", "", $node);
			}
			else {
				return false;
			}
		}

		if (is_array($route)) {
			if (!isset($route[self::root]))
				return false;

			$route = $route[self::root];
		}

		$class = $ctrlRoot . "\\" . $route . $ctrl;

		if (!self::checkMethod($class, $method, count($args)))
			return false;

		self::reply($class, $method, $args);

		return true;
	}


	public static function request($routes, $ctrlRoot="ctrl")
	{
		if (!self::handleRequest($routes, $ctrlRoot))
			throw new \Exception("not found", 404);
	}
}
