<?php

namespace aurora\http;


class request
{
	public $client;
	public $method;
	public $path;
	public $params;
	public $host;
	public $content;


	function __construct()
	{
		$uri  = $_SERVER["REQUEST_URI"];
		$path = strstr($uri, "?", true);

		$this->client = $_SERVER["REMOTE_ADDR"];
		$this->method = strtolower($_SERVER["REQUEST_METHOD"]);
		$this->path   = $path ? $path : $uri;
		$this->params = $_REQUEST;
		$this->host   = $_SERVER["HTTP_HOST"];

		if (isset($_SERVER["CONTENT_TYPE"])) {

			$this->content = client::decodeContentType($_SERVER["CONTENT_TYPE"]);

			switch ($this->content->type) {

			case "application/json":
				$this->params = (array)json_decode(file_get_contents("php://input"));
				break;
			}
		}
	}


	function get($key, $placeholder=NULL)
	{		
		if (isset($this->params[$key]))
			return $this->params[$key];
		else if (func_num_args() == 1)
			throw new \Exception("$key must be provided", 400);
		else
			return $placeholder;
	}
}
