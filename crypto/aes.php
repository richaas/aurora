<?php

namespace aurora\crypto;

use aurora\file\file;
use aurora\file\util as futl;
use aurora\util\rand;
use Exception;


class aes
{
	const method = "aes-256-ctr";
	const ivsize = 16;
	const blocksize = 65536;


	public static function encrypt($data, $key, $raw=false)
	{
		$iv = rand::bytes(self::ivsize);

		$res = openssl_encrypt($data, self::method,
				       hash("sha256", $key),
				       OPENSSL_RAW_DATA, $iv);
		if ($res === false)
			throw new Exception("aes: unable to encrypt data");

		$res .= $iv;

		return $raw ? $res : base64_encode($res);
	}


	public static function decrypt($data, $key, $raw=false)
	{
		$raw = $raw ? $data : base64_decode($data);

		if (strlen($raw) < self::ivsize)
			return false;

		$iv = substr($raw, -self::ivsize);

		return openssl_decrypt(substr($raw, 0, -self::ivsize), self::method,
				       hash("sha256", $key),
				       OPENSSL_RAW_DATA, $iv);
	}


	public static function encryptFile($path, $key)
	{
		$src = new file($path, "r");
		$dst = new file($path . "~", "w");

		while (strlen($data = $src->read(self::blocksize - self::ivsize)))
			$dst->write(self::encrypt($data, $key, true));

		futl::rename($path . "~", $path . "_");
	}


	public static function decryptFile($path, $key)
	{
		$dstPath = rtrim($path, "_");
		$src = new file($path, "r");
		$dst = new file($dstPath . "~", "w");

		while (strlen($data = $src->read(self::blocksize)))
			$dst->write(self::decrypt($data, $key, true));

		futl::rename($dstPath . "~", $dstPath);
	}
}
