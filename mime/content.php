<?php

namespace aurora\mime;

use aurora\file\util as futl;
use aurora\util\rand;


class content
{
	private $boundary;
	public $headers;
	public $data;


	public function __construct($type, $data=NULL, $disposition=NULL, $id=NULL)
	{
		$this->headers = array("Content-Type" => $type);

		if ($data) {
			$this->headers["Content-Transfer-Encoding"] = "base64";
			$this->data = chunk_split(base64_encode($data));
		}

		if ($disposition)
			$this->headers["Content-Disposition"] = $disposition;

		if ($id)
			$this->headers["Content-ID"] = sprintf("<%s>", $id);
	}


	public function add(content $content)
	{
		if (!$this->boundary) {

			$this->boundary = rand::string(32);
			$this->data     = sprintf("--%s\r\n", $this->boundary);
			$this->headers["Content-Type"] .= sprintf(";\r\n boundary=\"%s\"",
								  $this->boundary);
		}
		else
			$this->data = rtrim($this->data, "-\r\n") . "\r\n";


		foreach ($content->headers as $key => $value)
			$this->data .= sprintf("%s: %s\r\n", $key, $value);

		$this->data .= sprintf("\r\n%s--%s--\r\n", $content->data, $this->boundary);
	}


	public static function text($data)
	{
		return new content("text/plain; charset=utf-8", $data);
	}


	public static function html($data)
	{
		return new content("text/html; charset=utf-8", $data);
	}


	public static function file($filepath, $disposition="attachment", $id=NULL)
	{
		$mimeType = mime_content_type($filepath);
		if (!$mimeType)
			$mimeType = "application/octet-stream";

		return new content($mimeType, futl::file_get_contents($filepath),
				   sprintf("%s;\r\n filename=\"%s\"", $disposition, basename($filepath)), $id);
	}
}
