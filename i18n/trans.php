<?php

namespace aurora\i18n;


class trans
{
	private $msgs = [];
	private $res;


	public function __construct($lang)
	{
		$class = "\\lang\\" . preg_replace("/[^\w]/", "", $lang);

		$this->res = class_exists($class) ? new $class : $this;
	}


	private function format($msg, $args)
	{
		$msg = str_replace("%%", "%% ", $msg);

		foreach ($args as $idx => $arg)
			$msg = str_replace("%$idx", (string)$arg, $msg);

		return str_replace("%% ", "%", $msg);
	}


	private function plural($num)
	{
		return $num !== 1;
	}


	public function gettext($id, ...$args)
	{
		$msg = $this->res->msgs[$id][0] ?? $id;

		return count($args) > 0 ? $this->format($msg, $args) : $msg;
	}


	public function pgettext($ctx, $id, ...$args)
	{
		$msg = $this->res->msgs["$ctx\x04$id"][0] ?? $id;

		return count($args) > 0 ? $this->format($msg, $args) : $msg;
	}


	public function ngettext($id, $idp, $num, ...$args)
	{
		$idx = (int)$this->res->plural((int)$num);

		$msg = $this->res->msgs[$id][$idx] ?? ((int)$num === 1 ? $id : $idp);

		array_unshift($args, $num);

		return $this->format($msg, $args);
	}
}
