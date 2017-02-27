<?php

namespace aurora\saml;

use DOMDocument;
use Exception;


abstract class message
{
	public $doc;


	public function __construct()
	{
		$this->doc = new DOMDocument();
	}


	protected function decode($xml)
	{
		if (!$this->doc->loadXML($xml))
			throw new Exception("parse error", 400);

		return new decoder($this->doc);
	}
}
