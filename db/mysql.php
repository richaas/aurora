<?php

namespace aurora\db;

use mysqli;
use Exception;


class mysql
{
	public $conn;


	private function throw($obj)
	{
		switch ($obj->errno) {

		case 1213: throw new DeadlockException($obj->error);
		case 1048: throw new Exception(preg_replace("/Column '(\w+)'/", "field $1", $obj->error), 400);
		case 1062: throw new Exception(preg_replace("/Duplicate entry '(.*)' for key.+/",
							    "duplicate entry: $1", $obj->error), 400);
		case 1264: throw new Exception(preg_replace("/Out of range value for column '(\w+)'.+/",
							    "out of range value for field $1", $obj->error), 400);
		case 1366: throw new Exception(preg_replace("/Incorrect (.+) column `\w+`\.`(\w+)`\.`(\w+)` at row .+/",
							    "incorrect $1 field $2.$3", $obj->error), 400);
		case 1451: throw new Exception(preg_replace("/.+ key constraint fails \(`\w+`\.`(\w+)`.+/",
							    "$1 child records exist", $obj->error), 403);
		case 1452: throw new Exception(preg_replace("/.+ REFERENCES `(\w+)` .+/",
							    "parent $1 not found", $obj->error), 404);
		case 4025: throw new Exception(preg_replace("/CONSTRAINT `(.+)` failed for .+/",
							    "invalid value for $1", $obj->error), 400);
		default:   throw new Exception($obj->error);
		}
	}


	function __construct($host, $user, $pass, $db)
	{
		$this->conn = new mysqli($host, $user, $pass, $db);
		if ($this->conn->connect_errno)
			throw new Exception($this->conn->connect_error);

		$this->conn->set_charset("utf8");
		$this->conn->autocommit(false);
	}


	function __destruct()
	{
		$this->conn->close();
	}


	public function query($sql, $params=NULL)
	{
		$stmt = $this->conn->stmt_init();

		if (!$stmt->prepare($sql))
			$this->throw($this->conn);

		if (!is_array($params)) {
			$params = func_get_args();
			array_shift($params);
		}

		$bind = array("");

		foreach ($params as &$value) {

			if (is_bool($value) || is_int($value))
				$type = "i";
			else if (is_float($value))
				$type = "d";
			else
				$type = "s";

			$bind[0] .= $type;
			$bind[] = &$value;
		}

		if (count($bind) > 1 && !call_user_func_array(array($stmt, "bind_param"), $bind))
			throw new Exception("mysql bind error");

		if (!$stmt->execute())
			$this->throw($stmt);

		$res = $stmt->get_result();

		$stmt->close();

		if (!$res) {
			if ($this->conn->errno)
				$this->throw($this->conn);

			return;
		}

		$rows = $res->fetch_all(MYSQLI_ASSOC);

		$res->free();

		return $rows;
	}


	public function commit()
	{
		if (!$this->conn->commit())
			$this->throw($this->conn);
	}


	public function rollback()
	{
		if (!$this->conn->rollback())
			$this->throw($this->conn);
	}


	public function insertId()
	{
		return $this->conn->insert_id;
	}
}
