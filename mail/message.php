<?php

namespace aurora\mail;


class message
{
	private $to;
	private $subject;
	private $content;
	private $headers;


	private static function encode($value)
	{
		if (!is_array($value))
			return $value;

		$list = "";

		foreach ($value as $entry)
			$list .= "$entry, ";

		return substr($list, 0, -2);
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
		$this->subject = $subject;
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
		$this->setHeader("From", $from);
	}


	public function setContent($content, $contentType="text/plain")
	{
		$this->setHeader("Content-Type", $contentType);

		$this->content = wordwrap($content, 70, "\r\n");
	}


	public function send()
	{
		$headers = "";

		foreach ($this->headers as $name => $value)
			$headers .= "$name: $value\r\n";

		mail($this->to, $this->subject, $this->content, $headers);
	}
}
