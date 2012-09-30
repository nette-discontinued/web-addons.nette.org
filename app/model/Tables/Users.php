<?php

namespace NetteAddons\Model;

use Nette\Object;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection as TableSelection;
use Nette\Security\Identity;


/**
  -- view sql
  create view users_view as
  select users.*, users_details.created, users_details.apiToken, groups.g_title as role
  from users
  join users_details on users.id = users_details.id
  join groups on groups.g_id = users.group_id
*/

/**
 * Users repository
 */
class Users extends Table
{
	/** @var string */
	protected $tableName = 'users_view';



	/**
	 * Finds one user by name or by e-mail.
	 *
	 * @param  string user name or e-mail
	 * @return ActiveRow
	 */
	public function findOneByName($name)
	{
		return $this->getTable()
			->where('username = ? OR email = ?', $name, $name)
			->fetch();
	}



	/**
	 * Creates new user. When email or username is taken, returns false.
	 *
	 * @param  array
	 * @return ActiveRow|FALSE
	 */
	public function register(array $values)
	{
		throw new \NetteAddons\DeprecatedException('This should not be used at all.');
		return $this->createRow($values);
	}



	/**
	 * @param  ActiveRow
	 * @return Identity
	 */
	public function createIdentity(ActiveRow $user)
	{
		$data = $user->toArray();
		unset($data['password']);

		return new Identity($user->id, $user->role, $data);
	}


	/**
	 * Returns selection of all addons authors.
	 *
	 * @return TableSelection
	 */
	public function findAuthors()
	{
		$users = $this->connection->table('addons')->select('DISTINCT(userId)');
		return $this->findAll()->where('id', $users);
	}



	/**
	 * Create new user record (temporary)
	 * @param  int
	 * @param  string
	 * @param  string
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function createUser($id, $username, $password)
	{
		return $this->connection->table('users')->insert(array(
			'id' => $id,
			'username' => $username,
			'password' => sha1($password),
			'group_id' => 4,
		));
	}

}
