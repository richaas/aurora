<?php

namespace aurora\file;


class file
{
	public $fp;


	public function __construct($filename, $mode="r", $errmsg=NULL, $errcode=0)
	{
		$this->fp = fopen($filename, $mode);
		if ($this->fp === false)
			throw new \Exception($errmsg ?? error_get_last()["message"], $errcode);
	}


	public function __destruct()
	{
		fclose($this->fp);
	}


	public function seek($offset, $whence=SEEK_SET)
	{
		if (fseek($this->fp, $offset, $whence) !== 0)
			throw new \Exception(error_get_last()["message"]);
	}


	public function read($length)
	{
		$data = fread($this->fp, $length);
		if ($data === false)
			throw new \Exception(error_get_last()["message"]);

		return $data;
	}


	public function write($data, $length=PHP_INT_MAX)
	{
		$ret = fwrite($this->fp, $data, $length);
		if ($ret === false)
			throw new \Exception(error_get_last()["message"]);

		return $ret;
	}


	public function truncate($size)
	{
		if (!ftruncate($this->fp, $size))
			throw new \Exception(error_get_last()["message"]);
	}


	public function stat()
	{
		$stat = fstat($this->fp);
		if (!$stat)
			throw new \Exception(error_get_last()["message"]);

		return (object)array_slice($stat, count($stat)/2);
	}


	public function lock($exclusive, $block=false)
	{
		$mode = $exclusive ? LOCK_EX : LOCK_SH;

		if (!$block)
			$mode |= LOCK_NB;

		$ret = flock($this->fp, $mode, $wouldblock);
		if (!$ret && !$wouldblock)
			throw new \Exception(error_get_last()["message"]);

		return $ret;
	}


	public function unlock()
	{
		if (!flock($this->fp, LOCK_UN))
			throw new \Exception(error_get_last()["message"]);
	}
}
