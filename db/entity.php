<?php

namespace aurora\db;

use lib\db;


class entity
{
	const FOR_UPDATE = 1;
	const FOR_SHARE  = 2;


	protected function join(&$sql1, &$sql2, &$jc, $join, $target)
	{
		foreach($join as $key => $value) {

			if (!is_array($value))
				$key = $value;

			$comp = explode(":", $key);

			if (count($comp) > 1)
				$key = $comp[1];

			$entity = $comp[0];
			$alias  = $key[0] . $jc++;
			$class  = "\\model\\$key";

			$this->$entity = new $class(NULL);

			foreach ($this->$entity as $field => $foo)
				$sql1 .= ",$alias.$field $alias" . $field;

			$sql2 .= " LEFT JOIN $key $alias ON $alias.$key"."_id=$target.$entity"."_id";

			$this->$entity->__alias = $alias;

			if (is_array($value))
				$this->$entity->join($sql1, $sql2, $jc, $value, $alias);
		}
	}


	protected function storeResult($result, $prefix="")
	{
		foreach ($this as $key => $value) {

			if (is_object($value) && get_parent_class($value) === "aurora\\db\\entity") {

				$id = $key . "_id";

				if ($this->$id === NULL) {
					unset($this->$key);
					continue;
				}

				if (!isset($value->__alias))
					continue;

				$alias = $value->__alias;
				unset($value->__alias);

				$value->storeResult($result, $alias);
			}
			else
				$this->$key = $result[$prefix . $key];
		}

		$this->decode();
	}


	protected function encode()
	{
	}


	protected function decode()
	{
	}


	public function entity()
	{
		return (new \ReflectionClass($this))->getShortName();
	}


	public function select($id, $join=array(), $handleNotFound=false, $flags=0)
	{
		$entity = $this->entity();

		if (!is_array($id))
			$id = array($entity . "_id" => $id);

		$sql1 = "SELECT e.*";
		$sql2 = " FROM $entity e";
		$jc=0; $this->join($sql1, $sql2, $jc, $join, "e");
		$sql2 .= " WHERE";

		foreach ($id as $key => $value)
			$sql2 .= " e.$key=? AND";

		$sql2 = substr($sql2, 0, -4);

		if ($flags & self::FOR_UPDATE)
			$sql2 .= " FOR UPDATE";
		if ($flags & self::FOR_SHARE)
			$sql2 .= " LOCK IN SHARE MODE";

		$res = db::conn()->query($sql1 . $sql2, $id);
		if (count($res) < 1) {
			if ($handleNotFound)
				return NULL;
			else
				throw new \Exception("$entity not found", 404);
		}

		$this->storeResult($res[0]);

		return $this;
	}


	public function selectForUpdate($id, $join=array(), $handleNotFound=false)
	{
		return $this->select($id, $join, $handleNotFound, self::FOR_UPDATE);
	}


	public function selectForShare($id, $join=array(), $handleNotFound=false)
	{
		return $this->select($id, $join, $handleNotFound, self::FOR_SHARE);
	}


	public function insert($commit=true)
	{
		$this->encode();

		$entity   = $this->entity();
		$entityId = $entity . "_id";

		$sql1 = "INSERT INTO $entity(";
		$sql2 = " VALUES(";

		foreach ($this as $key => $value) {

			if (is_object($value) && get_parent_class($value) === "aurora\\db\\entity")
				continue;

			$sql1 .= "$key,";
			$sql2 .= "?,";
			$params[] = $value;
		}

		$sql1[strlen($sql1) - 1] = ')';
		$sql2[strlen($sql2) - 1] = ')';

		$dbc = db::conn();

		$dbc->query($sql1 . $sql2, $params);

		if ($this->$entityId === NULL)
			$this->$entityId = $dbc->insertId();

		if ($commit)
			$dbc->commit();

		$this->decode();
	}


	public function update($commit=true)
	{
		$this->encode();

		$entity   = $this->entity();
		$entityId = $entity . "_id";
		$params   = array();

		$sql = "UPDATE $entity SET ";

		foreach ($this as $key => $value) {

			if (is_object($value) && get_parent_class($value) === "aurora\\db\\entity")
				continue;

			if ($key === $entityId)
				continue;

			$sql .= "$key=?,";
			$params[] = $value;
		}

		$sql  = substr($sql, 0, -1);
		$sql .= " WHERE $entityId=?";
		$params[] = $this->$entityId;

		$dbc = db::conn();

		$dbc->query($sql, $params);

		if ($commit)
			$dbc->commit();

		$this->decode();
	}


	public function delete($commit=true)
	{
		$entity   = $this->entity();
		$entityId = $entity . "_id";

		$dbc = db::conn();

		$dbc->query("DELETE FROM $entity WHERE $entityId=?", $this->$entityId);

		if ($commit)
			$dbc->commit();
	}
}
