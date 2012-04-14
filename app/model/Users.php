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

	/**
	 * @var string
	 */
	protected $table = 'users';



	/**
	 * @param string $name name or email of user
	 *
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function findOneByName($name)
	{
		return $this->database->table('users')
			->where('name = ? OR email = ?', $name, $name)
			->fetch();
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
	 * Creates new user. When email or username is taken, returns false
	 *
	 * @param array $values
	 *
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function register(array $values)
	{
		try {
			return $this->database->table('users')->insert($values);

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
