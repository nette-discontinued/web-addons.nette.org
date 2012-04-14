<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Table extends Nette\Object
{

	/**
	 * @var \Nette\Database\Connection
	 */
	protected $database;

	/**
	 * @var string
	 */
	protected $table;



	/**
	 * @param \Nette\Database\Connection $db
	 */
	public function __construct(Nette\Database\Connection $db)
	{
		$this->database = $db;
	}



	/**
	 * @param array $by
	 *
	 * @return \Nette\Database\Table\Selection
	 */
	public function findBy(array $by)
	{
		return $this->database->table($this->table)->where($by);
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


}
