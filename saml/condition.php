<?php

namespace aurora\saml;

use DateTime;
use Exception;


class condition
{
	public $notBefore;
	public $notAfter;


	public function decode($dec, $node)
	{
		$this->notBefore = new DateTime($dec->getAttribute($node, "NotBefore"));
		$this->notAfter  = new DateTime($dec->getAttribute($node, "NotOnOrAfter"));
	}


	public function validate()
	{
		$now = time();

		if ($this->notBefore->getTimestamp() > $now || $this->notAfter->getTimestamp() < $now)
			throw new Exception("condition time-range violation", 400);
	}
}
