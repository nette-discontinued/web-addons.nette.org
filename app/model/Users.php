<?php

namespace NetteAddons\Model;

use Nette\Object;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Identity;



/**
 * User model
 */
class Users extends Table
{

	/**
	 * @var string
	 */
	protected $tableName = 'users';



	/**
	 * @param string $name name or email of user
	 *
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function findOneByName($name)
	{
		return $this->getTable()
			->where('name = ? OR email = ?', $name, $name)
			->fetch();
	}



	/**
	 * Creates new user. When email or username is taken, returns false
	 *
	 * @param array $values
	 *
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function register(array $values)
	{
		try {
			return $this->createRow($values);

		} catch (\PDOException $e) {
			return FALSE;
		}
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $user
	 *
	 * @return \Nette\Security\Identity
	 */
	public function createIdentity(ActiveRow $user)
	{
		$data = $user->toArray();
		unset($user['password']);

		return new Identity($user->id, NULL, $data);
	}

}
