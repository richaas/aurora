<?php

namespace aurora\db;


class mysql
{
	public $conn;


	function __construct($host, $user, $pass, $db)
	{
		$this->conn = new \mysqli($host, $user, $pass, $db);
		if ($this->conn->connect_errno)
			throw new \Exception($this->conn->connect_error);

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
			throw new \Exception($this->conn->error);

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
			throw new \Exception("mysql bind error");

		if (!$stmt->execute())
			throw new \Exception($stmt->error);

		$res = $stmt->get_result();

		$stmt->close();

		if (!$res) {
			if ($this->conn->errno)
				throw new \Exception($this->conn->error);

			return;
		}

		$rows = $res->fetch_all(MYSQLI_ASSOC);

		$res->free();

		return $rows;
	}


	public function commit()
	{
		if (!$this->conn->commit())
			throw new \Exception($this->conn->error);
	}


	public function rollback()
	{
		if (!$this->conn->rollback())
			throw new \Exception($this->conn->error);
	}


	public function insertId()
	{
		return $this->conn->insert_id;
	}
}
