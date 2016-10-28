<?php

namespace aurora\http;


class request
{
	public $client;
	public $method;
	public $path;
	public $uri;
	public $param;
	public $host;
	public $scheme;
	public $referer;
	public $content;


	function __construct()
	{
		$uri  = $_SERVER["REQUEST_URI"];
		$path = strstr($uri, "?", true);

		$this->client  = $_SERVER["REMOTE_ADDR"];
		$this->method  = strtolower($_SERVER["REQUEST_METHOD"]);
		$this->path    = $path ? $path : $uri;
		$this->uri     = $uri;
		$this->param   = (object)$_REQUEST;
		$this->host    = $_SERVER["HTTP_HOST"];
		$this->scheme  = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
		$this->referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : NULL;

		if (isset($_SERVER["CONTENT_TYPE"])) {

			$this->content = client::decodeContentType($_SERVER["CONTENT_TYPE"]);

			switch ($this->content->type) {

			case "application/json":
				$param = json_decode(file_get_contents("php://input"));
				if ($param !== NULL)
					$this->param = $param;
				break;
			}
		}
	}


	function get($key, $placeholder=NULL)
	{		
		if (property_exists($this->param, $key))
			return $this->param->$key;
		else if (func_num_args() == 1)
			throw new \Exception("parameter $key must be provided", 400);
		else
			return $placeholder;
	}


	function getParam($key, &$value=NULL)
	{
		if (!property_exists($this->param, $key))
			return false;

		$value = $this->param->$key;

		return true;
	}
}
