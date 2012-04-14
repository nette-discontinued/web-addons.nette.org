<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;



/**
 * @property \Nette\Database\Table\Selection $table
 */
abstract class Table extends Nette\Object
{

	/**
	 * @var \Nette\Database\Connection
	 */
	protected $database;



	/**
	 * @param \Nette\Database\Connection $db
	 */
	public function __construct(Nette\Database\Connection $db)
	{
		if (!isset($this->tableName)) {
			$class = get_called_class();
			throw new Nette\InvalidStateException("Property \$tableName must be defined in $class.");
		}

		$this->database = $db;
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->database->table($this->tableName);
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
	 *
	 * @return \Nette\Database\Table\Selection
	 */
	public function findBy(array $by)
	{
		return $this->getTable()->where($by);
	}



	/**
	 * @param array $by
	 *
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function findOneBy(array $by)
	{
		return $this->findBy($by)->limit(1)->fetch();
	}



	/**
	 * Updates user with values
	 *
	 * @param \Nette\Database\Table\ActiveRow $user
	 * @param array $values
	 */
	public function update(ActiveRow $user, array $values)
	{
		// todo validate values
		$user->update($values);
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow|\Nette\Database\Table\Selection $selection
	 * @throws \Nette\InvalidArgumentException
	 * @return boolean
	 */
	public function remove($selection)
	{
		try {
			if ($selection instanceof Selection) {
				if ($selection->getName() !== $this->tableName) {
					throw new Nette\InvalidArgumentException;
				}

				/** @var Selection $selection */
				$selection->delete();

			} elseif ($selection instanceof ActiveRow) {
				/** @var ActiveRow $selection */
				$selection->delete();

			} else {
				throw new Nette\InvalidArgumentException;
			}

			return TRUE;

		} catch (\PDOException $e) {
			return FALSE;
		}
	}



	/**
	 * @param array $values
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function createRow(array $values)
	{
		return $this->getTable()->insert($values);
	}

}
