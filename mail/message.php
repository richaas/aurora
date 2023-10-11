<?php

namespace aurora\mail;

use aurora\mime\content;
use Exception;


class message
{
	private $to;
	private $from;
	private $subject;
	private $content;
	private $headers;


	private static function encode($value)
	{
		return is_array($value) ? implode(", ", $value) : $value;
	}


	public function __construct($to=NULL, $subject=NULL, $from=NULL, $content=NULL)
	{
		$this->headers = array();

		if (isset($to))
			$this->setTo($to);

		if (isset($subject))
			$this->setSubject($subject);

		if (isset($from))
			$this->setFrom($from);

		if (isset($content))
			$this->setContent($content);
	}


	public function setHeader($name, $value)
	{
		$this->headers[$name] = $value;
	}


	public function setSubject($subject)
	{
		mb_internal_encoding("UTF-8");
		$this->subject = mb_encode_mimeheader($subject, "UTF-8", "B", "\r\n", 9);
	}


	public function setTo($to)
	{
		$this->to = self::encode($to);
	}


	public function setCc($cc)
	{
		$this->setHeader("Cc", self::encode($cc));
	}


	public function setBcc($bcc)
	{
		$this->setHeader("Bcc", self::encode($bcc));
	}


	public function setFrom($from)
	{
		if (is_array($from)) {
			$this->setHeader("From", "$from[0] <$from[1]>");
			$this->from = $from[1];
		}
		else {
			$this->setHeader("From", $from);
			$this->from = $from;
		}
	}


	public function setContent($content)
	{
		if (is_string($content))
			$content = content::text($content);

		$this->setHeader("MIME-Version", "1.0");
		$this->headers += $content->headers;
		$this->content = $content->data;
	}


	public function _send()
	{
		return mail($this->to, $this->subject, $this->content, $this->headers, "-f {$this->from}");
	}


	public function send()
	{
		if (!$this->_send())
			throw new Exception("unable to send mail message");
	}
}
