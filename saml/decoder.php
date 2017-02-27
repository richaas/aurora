<?php

namespace aurora\saml;

use DOMXPath;
use Exception;


class decoder
{
	const NS_ASSERTION = "urn:oasis:names:tc:SAML:2.0:assertion";
	const NS_XMLDSIG   = "http://www.w3.org/2000/09/xmldsig#";

	private $xpath;


	public function __construct($doc)
	{
		$this->xpath = new DOMXpath($doc);

		$this->xpath->registerNamespace("saml", self::NS_ASSERTION);
		$this->xpath->registerNamespace("ds",   self::NS_XMLDSIG);
	}


	public function query($name, $node=NULL)
	{
		return $this->xpath->query("./" . $name, $node);
	}


	public function getNode($name, $node=NULL)
	{
		$res = $this->query($name, $node);
		if (!$res->length)
			throw new Exception("$name node not found", 400);

		return $res->item(0);
	}


	public function getAttribute($node, $name)
	{
		$val = $node->getAttribute($name);
		if ($val === "")
			throw new Exception("$name attribute not found", 400);

		return $val;
	}
}
