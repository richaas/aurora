<?php

namespace aurora\http;


class response
{
	public $content;
	public $status;
	public $headers;
	public $filename;


	function __construct($content="", $status=200, $headers=array(), $filename=NULL)
	{
		$this->content  = $content;
		$this->status   = $status;
		$this->headers  = $headers;
		$this->filename = $filename;
	}
}
