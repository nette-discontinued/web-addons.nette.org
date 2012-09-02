<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;



/**
 * Represents repository for database table
 */
abstract class Table extends Nette\Object
{

	/** @var \Nette\Database\Connection */
	protected $connection;



	/**
	 * @param \Nette\Database\Connection $db
	 * @throws \NetteAddons\InvalidStateException
	 */
	public function __construct(Nette\Database\Connection $db)
	{
		if (!isset($this->tableName)) {
			$class = get_called_class();
			throw new \NetteAddons\InvalidStateException("Property \$tableName must be defined in $class.");
		}

		$this->connection = $db;
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->connection->table($this->tableName);
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function findAll()
	{
		return $this->getTable();
	}



	/**
	 * @param array $by
	 * @return \Nette\Database\Table\Selection
	 */
	public function findBy(array $by)
	{
		return $this->getTable()->where($by);
	}



	/**
	 * @param array $by
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function findOneBy(array $by)
	{
		return $this->findBy($by)->limit(1)->fetch();
	}



	/**
	 * @param int $id
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function find($id)
	{
		return $this->findOneBy(array('id' => $id));
	}



	/**
	 * Creates and inserts new row to database.
	 *
	 * @param  array row values
	 * @return \Nette\Database\Table\ActiveRow created row
	 * @throws \NetteAddons\DuplicateEntryException
	 * @throws \PDOException in case of SQL / database error
	 */
	protected function createRow(array $values)
	{
		try {
			return $this->getTable()->insert($values);

		} catch (\PDOException $e) {
			if ($e->getCode() == 23000) {
				throw new \NetteAddons\DuplicateEntryException(NULL, NULL, $e);
			} else {
				throw $e;
			}
		}
	}



	/**
	 * Insert row in database or update existing one.
	 *
	 * @param  array
	 * @return \Nette\Database\Table\ActiveRow automatically found based on first "column => value" pair in $values
	 */
	public function createOrUpdate(array $values)
	{
		$pairs = array();
		foreach ($values as $key => $value) {
			$pairs[] = "`$key` = ?"; // warning: SQL injection possible if $values infected!
		}

		$pairs = implode(', ', $pairs);
		$values = array_values($values);

		$this->connection->queryArgs(
			'INSERT INTO `' . $this->tableName . '` SET ' . $pairs .
			' ON DUPLICATE KEY UPDATE ' . $pairs, array_merge($values, $values)
		);

		return $this->findOneBy(func_get_arg(0));
	}

}
