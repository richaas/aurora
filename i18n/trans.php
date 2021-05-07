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

		for ($idx=1; $idx<count($args); $idx++)
			$msg = str_replace("%$idx", (string)$args[$idx], $msg);

		return str_replace("%% ", "%", $msg);
	}


	private function plural($num)
	{
		return $num !== 1;
	}


	public function gettext($id)
	{
		$msg = $this->res->msgs[$id] ?? $id;

		$args = func_get_args();

		return count($args) > 1 ? $this->format($msg, $args) : $msg;
	}


	public function ngettext($id, $idp, $num)
	{
		$idx = (int)$this->res->plural((int)$num);

		$msg = $this->res->msgs[$id][$idx] ?? ((int)$num === 1 ? $id : $idp);

		$args = func_get_args();

		return $this->format($msg, array_slice($args, 1));
	}
}
