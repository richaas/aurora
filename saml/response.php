<?php

namespace aurora\saml;

use Exception;


class response extends message
{
	public $assertions = array();


	public function decode($xml)
	{
		$dec = parent::decode($xml);

		foreach ($dec->query("saml:Assertion") as $node) {

			$this->assertions[] = $assert = new assertion();

			$assert->decode($dec, $node);
		}

		if (count($this->assertions) < 1)
			throw new Exception("no assertions in response", 400);

		return $this;
	}
}
