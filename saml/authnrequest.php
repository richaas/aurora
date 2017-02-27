<?php

namespace aurora\saml;

use aurora\util\uuid;


class authnrequest
{
	public static function encode($acsUrl, $issuer)
	{
		$now = gmdate("Y-m-d\TH:i:s\Z");
		$id  = uuid::v4();

		return <<<XML
<samlp:AuthnRequest
 AssertionConsumerServiceURL="$acsUrl"
 ID="$id"
 IssueInstant="$now"
 Version="2.0"
 xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
 xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol">
<saml:Issuer>$issuer</saml:Issuer>
<samlp:NameIDPolicy
 AllowCreate="true"
 Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress"/>
</samlp:AuthnRequest>
XML;
	}
}
