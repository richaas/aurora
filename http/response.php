<?php

namespace aurora\http;


class response
{
	public $content;
	public $status;
	public $headers;


	function __construct($content="", $status=200, $headers=array())
	{
		$this->content = $content;
		$this->status  = $status;
		$this->headers = $headers;
	}
}
