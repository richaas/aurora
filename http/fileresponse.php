<?php

namespace aurora\http;


class fileresponse extends response
{
	protected $filename;
	protected $offset;
	protected $maxlen;


	private static function lastModified($mtime)
	{
		$date = new \DateTime();

		$date->setTimestamp($mtime);
		$date->setTimezone(new \DateTimeZone("UTC"));

		return $date->format("D, d M Y H:i:s") . " GMT";
	}


	private function setRange($size, $etag)
	{
		if ($size < 1)
			return false;

		if (!isset($_SERVER["HTTP_RANGE"]))
			return false;

		if (isset($_SERVER["HTTP_IF_RANGE"]) && $_SERVER["HTTP_IF_RANGE"] !== $etag)
			return false;

		if (!preg_match("/bytes=(\d*)-(\d*)/", $_SERVER["HTTP_RANGE"], $res))
			return false;

		$endMax = $size - 1;

		if ($res[1] !== "") {

			$start = (int)$res[1];

			if ($start > $endMax) {
				header("Content-Range: bytes */" . $size);
				throw new \Exception("requested range not satisfiable", 416);
			}

			$end = ($res[2] === "") ? $endMax : min((int)$res[2], $endMax);
		}
		else if ($res[2] !== "") {
			$end   = $endMax;
			$start = $size - min((int)$res[2], $size);
		}
		else
			return false;

		if ($start > $end)
			return false;

		$this->offset = $start;
		$this->maxlen = $end - $start + 1;

		$this->status = 206;
		$this->headers[] = "Content-Range: " . sprintf("bytes %s-%s/%s", $start, $end, $size);
		$this->headers[] = "Content-Length: " . $this->maxlen;

		return true;
	}


	protected function setHeaders($mimeType, $size, $inode, $mtime)
	{
		$etag = sprintf("\"%x-%x-%x\"", $inode, $size, $mtime);

		$this->headers[] = "Last-Modified: " . self::lastModified($mtime);
		$this->headers[] = "Content-Type: " . $mimeType;
		$this->headers[] = "Accept-Ranges: bytes";
		$this->headers[] = "ETag: " . $etag;

		$this->offset = 0;
		$this->maxlen = -1;

		if (!$this->setRange($size, $etag))
			$this->headers[] = "Content-Length: " . $size;
	}


	public function __construct($filename, $status=200, $headers=array(), $mimeType=NULL)
	{
		$this->filename = $filename;
		$this->status   = $status;
		$this->headers  = $headers;

		$stat = stat($filename);
		if (!$stat || ($stat["mode"] & 0xf000) != 0x8000)
			throw new \Exception("file not found", 404);

		if (!$mimeType) {
			$mimeType = mime_content_type($filename);
			if (!$mimeType)
				$mimeType = "application/octet-stream";
		}

		$this->setHeaders($mimeType, $stat["size"], $stat["ino"], $stat["mtime"]);
	}


	public function sendContent()
	{
		if ($this->maxlen === 0)
			return;

		$out  = fopen("php://output", "wb");
		$file = fopen($this->filename, "rb");

		stream_copy_to_stream($file, $out, $this->maxlen, $this->offset);

		fclose($out);
		fclose($file);
	}
}
