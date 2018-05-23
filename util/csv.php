<?php

namespace aurora\util;


class csv
{
	public static function encode($data)
	{
		$csv = "";

		foreach ($data as $entry) {

			$line = "";

			foreach ($entry as $value) {

				$line .= "\"" . str_replace("\"", "\"\"", $value) . "\",";
			}

			$csv .= rtrim($line, ",") . "\n";
		}

		return $csv;
	}
}
