<?php

namespace aurora\saml;


class assertion
{
	private $condition;
	private $signature;
	public $attributes = array();


	public function __construct()
	{
		$this->condition = new condition();
		$this->signature = new signature();
	}


	public function decode($dec, $node)
	{
		$this->condition->decode($dec, $dec->getNode("saml:Conditions", $node));
		$this->signature->decode($dec, $dec->getNode("ds:Signature", $node));

		foreach ($dec->query("saml:AttributeStatement/saml:Attribute", $node) as $attr) {

			$key = $dec->getAttribute($attr, "Name");

			$this->attributes[$key] = trim($attr->textContent);
		}
	}


	public function validate($cert)
	{
		$this->condition->validate();
		$this->signature->validate($cert);
	}
}
