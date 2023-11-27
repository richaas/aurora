<?php

namespace aurora\http;


class request extends param
{
	public $client;
	public $method;
	public $path;
	public $uri;
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
		$this->host    = $_SERVER["HTTP_HOST"] ?? NULL;
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

			case "multipart/form-data":
				if (!isset($_REQUEST["_json"]))
					break;

				$param = json_decode($_REQUEST["_json"]);
				if ($param !== NULL)
					$this->param = $param;
				break;
			}
		}
	}


	function file($path, $key, $placeholder=NULL)
	{
		if (isset($_FILES[$key])) {

			$file = (object)$_FILES[$key];

			$filename = preg_replace("/[^\w\.\-]/", "", basename($file->name));

			if (!move_uploaded_file($file->tmp_name, $path . "/" . $filename))
				throw new \Exception("file upload error: " . error_get_last()["message"]);

			return $filename;
		}
		else if (func_num_args() == 2)
			throw new \Exception("file $key must be provided", 400);
		else
			return $placeholder;
	}
}
