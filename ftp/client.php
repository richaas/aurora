<?php

namespace aurora\ftp;


class client
{
	private $curl;


	public function __construct()
	{
		$this->curl = curl_init();
		if (!$this->curl)
			throw new \Exception("curl_init(): failed");
	}


	public function __destruct()
	{
		curl_close($this->curl);
	}


	public function setVerifyPeer($verify)
	{
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, $verify);
	}


	public function setCredentials($username, $password)
	{
		curl_setopt($this->curl, CURLOPT_USERPWD, $username . ":" . $password);
	}


	public function getInfo()
	{
		return curl_getinfo($this->curl);
	}


	public function put($filename, $url)
	{
		$fp = fopen($filename, "rb");
		if (!$fp)
			throw new \Exception(error_get_last()["message"]);

		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_UPLOAD, true);
		curl_setopt($this->curl, CURLOPT_INFILE, $fp);
		curl_setopt($this->curl, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
		curl_setopt($this->curl, CURLOPT_FTP_CREATE_MISSING_DIRS, true);

		$res = curl_exec($this->curl);

		fclose($fp);

		if ($res === false)
			throw new \Exception(curl_error($this->curl));
	}
}
