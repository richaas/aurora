<?php

namespace aurora\cmd;


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

			$inst = new $class;

			printf("%-20s%s\n", $cmd, $inst->help());
                }
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

		if (!is_file($this->path($cmd) . ".php"))
			throw new \Exception($cmd . ": command not found");

		$class = $this->_class($cmd);

		call_user_func_array(array(new $class, "exec"), array_slice($argv, 2));
	}
}
