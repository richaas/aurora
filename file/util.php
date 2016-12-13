<?php

namespace aurora\file;

use Exception;


class util
{
	public static function link($target, $link)
	{
		if (!link($target, $link))
			throw new Exception("link($target, $link): " . error_get_last()["message"]);
	}


	public static function mkdir($pathname, $mode=0777, $recursive=false)
	{
		if (!mkdir($pathname, $mode, $recursive))
			throw new Exception("mkdir($pathname): " . error_get_last()["message"]);
	}


	public static function rename($old, $new)
	{
		if (!rename($old, $new))
			throw new Exception(error_get_last()["message"]);
	}


	public static function scandir($pathname, $sorting_order=SCANDIR_SORT_ASCENDING)
	{
		$ret = scandir($pathname, $sorting_order);
		if ($ret === false)
			throw new Exception("scandir($pathname): " . error_get_last()["message"]);

		return $ret;
	}


	public static function touch($filename)
	{
		if (!touch($filename))
			throw new Exception(error_get_last()["message"]);
	}


	public static function unlink($filename)
	{
		if (!file_exists($filename))
			return;

		if (!unlink($filename))
			throw new Exception(error_get_last()["message"]);
	}


	public static function file_put_contents($filename, $data, $flags=0)
	{
		if (file_put_contents($filename, $data, $flags) === false)
			throw new Exception(error_get_last()["message"]);
	}


	public static function file_get_contents($filename)
	{
		$data = file_get_contents($filename);
		if ($data === false)
			throw new Exception(error_get_last()["message"]);

		return $data;
	}
}
