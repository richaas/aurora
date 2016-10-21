<?php

namespace aurora\http;


class response
{
	private $content;
	public $status;
	public $headers;


	public function __construct($content="", $status=200, $headers=array())
	{
		$this->content  = $content;
		$this->status   = $status;
		$this->headers  = $headers;
	}


	public function sendContent()
	{
		echo $this->content;
	}
}
