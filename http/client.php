<?php

namespace aurora\http;


class client
{
	public $curl;
	private $headers;
	public $status;
	public $content;
	public $data;


	public function __construct()
	{
		$this->headers = array();

		$this->curl = curl_init();
		if (!$this->curl)
			throw new \Exception("curl_init(): failed");

		curl_setopt($this->curl, CURLOPT_HEADER, false);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
	}


	public function __destruct()
	{
		curl_close($this->curl);
	}


	public function setVerifyHost($verify)
	{
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, $verify);
	}


	public function setVerifyPeer($verify)
	{
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, $verify);
	}


	public function setCredentials($username, $password, $method=CURLAUTH_ANY)
	{
		curl_setopt($this->curl, CURLOPT_USERPWD, $username . ":" . $password);
		curl_setopt($this->curl, CURLOPT_HTTPAUTH, $method);
	}


	public function setHeaders($headers)
	{
		$this->headers = $headers;
	}


	public function get($url, $params=NULL)
	{
		if ($params)
			$url .= "?" . $this->encodeParams($params);

		return $this->request("GET", $url);
	}


	public function post($url, $data="", $contentType="application/json")
	{
		return $this->request("POST", $url, $data, $contentType);
	}


	public function put($url, $data="", $contentType="application/json")
	{
		return $this->request("PUT", $url, $data, $contentType);
	}


	public function patch($url, $data="", $contentType="application/json")
	{
		return $this->request("PATCH", $url, $data, $contentType);
	}


	public function delete($url)
	{
		return $this->request("DELETE", $url);
	}


	private function request($method, $url, $data=NULL, $contentType=NULL)
	{
		$upload = false;

		if ($data) {
			curl_setopt($this->curl, CURLOPT_HTTPHEADER,
				    array_merge($this->headers, array("Content-Type: $contentType")));

			if (is_resource($data)) {

				$upload = true;
				curl_setopt($this->curl, CURLOPT_UPLOAD, true);
				curl_setopt($this->curl, CURLOPT_INFILE, $data);

				$stat = fstat($data);

				if ($stat && ($stat["mode"] & 0x8000))
					curl_setopt($this->curl, CURLOPT_INFILESIZE, $stat["size"]);
			}
			else {
				curl_setopt($this->curl, CURLOPT_POSTFIELDS,
					    $this->encodeData($data, $contentType));
			}
		}
		else {
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
			curl_setopt($this->curl, CURLOPT_HTTPGET, true);
		}

		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($this->curl, CURLOPT_URL, $url);

		$this->data = curl_exec($this->curl);
		if ($upload) {
			curl_setopt($this->curl, CURLOPT_UPLOAD, false);
			curl_setopt($this->curl, CURLOPT_INFILE, NULL);
		}
		if ($this->data === false)
			throw new \Exception(curl_error($this->curl));

		$this->status  = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		$this->content = $this->decodeContentType(curl_getinfo($this->curl,
								       CURLINFO_CONTENT_TYPE));

		return $this->data;
	}


	private function encodeData($data, $contentType)
	{
		if (!is_array($data) && !is_object($data))
			return $data;

		switch ($contentType) {

		case "application/json":
			return json_encode($data);

		case "application/x-www-form-urlencoded":
			return $this->encodeParams($data);

		case "multipart/form-data":
			return $data;

		default:
			throw new \Exception("unsupported content-type: $contentType");
		}
	}


	private function encodeParams($params)
	{
		$data = "";

		foreach ($params as $key => $value)
			$data .= urlencode($key) . "=" . urlencode($value) . "&";

		return substr($data, 0, -1);
	}


	public static function decodeContentType($value)
	{
		$fields = explode(";", $value);

		$ctype["type"] = strtolower(trim($fields[0]));

		unset($fields[0]);

		foreach ($fields as $field) {

			$fld = explode("=", $field);

			$ctype[trim($fld[0])] = trim($fld[1]);
		}

		return (object)$ctype;
	}
}
