<?php

namespace aurora\http;

use aurora\crypto\aes;
use aurora\file\file;
use Exception;


class cryptofileresponse extends fileresponse
{
	private $key;


	public function __construct($filename, $mimeType, $key, $status=200, $headers=array())
	{
		$this->status   = $status;
		$this->headers  = $headers;

		if (!file_exists($filename)) {
			$filename .= "_";
			$this->key = $key;
		}

		$this->file = new file($filename, "rb", "file not found", 404);

		$stat = $this->file->stat();

		if (($stat->mode & 0xf000) != 0x8000)
			throw new Exception("file not found", 404);

		$size = $stat->size;

		if ($this->key)
			$size -= aes::ivsize * (int)ceil($size / aes::blocksize);

		$this->setHeaders($mimeType, $size, $stat->ino, $stat->mtime);

		$this->headers[] = sprintf("X-Encrypted: %d", $this->key !== NULL);
	}


	public function sendContent()
	{
		if (!$this->key)
			return parent::sendContent();

		if ($this->maxlen === 0)
			return;
		else if ($this->maxlen < 0)
			$bytes = PHP_INT_MAX;
		else
			$bytes = $this->maxlen;

		$out = new file("php://output", "wb");

		if ($this->offset > 0) {

			$bsize  = aes::blocksize - aes::ivsize;
			$block  = (int)($this->offset / $bsize);
			$offset = aes::blocksize * $block;
			$skip   = $this->offset - ($bsize * $block);

			$this->file->seek($offset);
		}
		else
			$skip = 0;

		while ($bytes > 0 && strlen($data = $this->file->read(aes::blocksize))) {

			$data = aes::decrypt($data, $this->key, true);

			if ($skip > 0) {
				$data = substr($data, $skip);
				$skip = 0;
			}

			$bytes -= $out->write($data, $bytes);
		}
	}
}
