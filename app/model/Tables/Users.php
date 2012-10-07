<?php

namespace NetteAddons\Model;

use Nette\Security\Identity,
	Nette\Database\Table\ActiveRow,
	Nette\Database\Table\Selection as TableSelection;


/**
 * Users repository
 */
class Users extends Table
{
	/** @var string */
	protected $tableName = 'users';



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

		$role = strtolower($user->ref('groups', 'group_id')->g_title);

		if ($details = $user->ref('users_details', 'id')) {
			$data += $details->toArray();
		}

		return new Identity($user->id, $role, $data);
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
			'realname' => $username,
			'password' => sha1($password),
			'group_id' => 4,
		));
	}

}
