<?php

namespace aurora\cmd;

use Exception;


class console
{
	private $root;
	private $base;


	private static function cmd($path, $cmd)
	{
		return $path ? $path . "/" . $cmd : $cmd;
	}


	private function path($cmd)
	{
		return $this->root . "/" . $this->base . "/" . $cmd;
	}


	private function _class($cmd)
	{
		return str_replace("/", "\\", $this->base . "/" . $cmd);
	}


	private function help($path)
	{
		foreach (scandir($this->path($path)) as $ent) {

			if ($ent[0] === '.')
				continue;

			$cmd = self::cmd($path, $ent);

			if (is_dir($this->path($cmd))) {
				$this->help($cmd);
				continue;
			}

			if (!preg_match("/^(\w+)\.php$/", $ent, $res))
				continue;

			$cmd = self::cmd($path, $res[1]);
			$class = $this->_class($cmd);

			printf("%-30s%s\n", $cmd, $class::desc);
		}
	}


	private static function usage($cmd, $params)
	{
		$usage = "usage: $cmd";

		foreach ($params as $param) {

			if ($param->isOptional())
				$usage .= " [" . $param->name . "]";
			else
				$usage .= " <" . $param->name . ">";
		}

		return $usage;
	}


	public function __construct($root, $base)
	{
		$this->root = $root;
		$this->base = $base;
	}


	public function exec($argv)
	{
		$cmd = isset($argv[1]) ? $argv[1] : "";

		if (is_dir($this->path($cmd)))
			return $this->help($cmd);

		$class = $this->_class($cmd);

		try {
			$rm = new \ReflectionMethod($class, "exec");
		}
		catch (\ReflectionException $ex) {
			throw new Exception($cmd . ": command not found");
		}

		if (!$rm->isPublic())
			throw new Exception($cmd . ": command not found");

		$args = array_slice($argv, 2);
		$argc = count($args);

		if ($argc < $rm->getNumberOfRequiredParameters() ||
		    $argc > $rm->getNumberOfParameters())
			throw new Exception(self::usage($cmd, $rm->getParameters()));

		call_user_func_array(array(new $class, "exec"), $args);
	}
}
