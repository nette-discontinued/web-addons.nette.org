<?php

namespace NetteAddons;

use Nette\Object;
use Nette\Database\Connection;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Identity;



/**
 * User model
 */
class Users extends Object
{

	/** @var \Nette\Database\Connection */
	private $database;



	/**
	 * @param \Nette\Database\Connection $db
	 */
	public function __construct(Connection $db)
	{
		$this->database = $db;
	}



	/**
	 * @param array $by
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function findBy(array $by)
	{
		return $this->database->table('users')->where($by)->fetch();
	}



	/**
	 * @param $name
	 *
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function findByName($name)
	{
		return $this->database->table('users')
			->where('name = ? OR email = ?', $name, $name)
			->fetch();
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $user
	 * @param array $values
	 */
	public function update(ActiveRow $user, array $values)
	{
		// todo validate values
		$user->update($values);
	}



	/**
	 * @param array $values
	 */
	public function register(array $values)
	{
		// todo validate values
		$this->database->table('users')->insert($values);
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $user
	 * @return \Nette\Security\Identity
	 */
	public function createIdentity(ActiveRow $user)
	{
		$data = $user->toArray();
		unset($user['password']);

		return new Identity($user->id, NULL, $data);
	}

}
