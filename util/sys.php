<?php

namespace aurora\util;


class sys
{
	public static function exec($cmd)
	{
		exec($cmd, $output, $status);

		if ($status !== 0)
			throw new \Exception(implode("\n", $output));
	}
}
