<?php

namespace aurora\saml;

use Exception;
use RobRichards\XMLSecLibs\XMLSecurityDSig;


class signature
{
	private $dsig;
	private $data;
	private $signature;


	private function signatureAlgorithm($cert)
	{
		$data = openssl_x509_parse($cert);

		if (!isset($data["signatureTypeSN"]))
			throw new Exception("failed to obtain certificate signature algorithm");

		return $data["signatureTypeSN"];
	}


	public function __construct()
	{
		$this->dsig = new XMLSecurityDSig();

		$this->dsig->idKeys[] = "ID";
	}


	public function decode($dec, $node)
	{
		$this->dsig->sigNode = $node;

		$this->data = $this->dsig->canonicalizeSignedInfo();
		$this->signature = base64_decode(trim($dec->getNode("ds:SignatureValue", $node)->textContent));
	}


	public function validate($cert)
	{
		if ($this->dsig->validateReference() !== true)
			throw new Exception("reference validation failed", 400);

		$algo = $this->signatureAlgorithm($cert);

		if (openssl_verify($this->data, $this->signature, $cert, $algo) !== 1)
			throw new Exception("signature verification failed", 400);
	}
}
