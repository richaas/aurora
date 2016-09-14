<?php

namespace aurora\http;


class controller
{
	private static function checkMethod($ctrl, $method, $argc)
	{
		if (!method_exists($ctrl, $method))
			return false;

		$rm = new \ReflectionMethod($ctrl, $method);

		if (!$rm->isPublic())
			return false;

		if ($argc < $rm->getNumberOfRequiredParameters() ||
		    $argc > $rm->getNumberOfParameters())
			return false;

		return true;
	}


	private static function reply($ctrl, $method, $args)
	{
		if (!self::checkMethod($ctrl, $method, count($args)))
			throw new \Exception("Method not allowed", 405);

		$resp = call_user_func_array(array(new $ctrl, $method), $args);
		if (!isset($resp))
			$resp = new response("", 204, array("Content-Type:"));

		header("HTTP/1.1 " . $resp->status);

		foreach ($resp->headers as $value)
			header($value);

		echo $resp->content;
	}


	public static function request($routes)
	{
		$req = new request();

		foreach ($routes as $rt) {

                        if (preg_match($rt["expr"], $req->path, $args)) {

				$method  = $req->method;
				$args[0] = $req;

				if (isset($rt["suffix"])) {
					$method .= $args[$rt["suffix"]];
					unset($args[$rt["suffix"]]);
				}

				return self::reply($rt["ctrl"], $method, $args);
			}
		}

		throw new \Exception("Not found", 404);
	}
}
