<?php

namespace NetteAddons\Model;

use Nette\Security\Identity;
use Nette\Database\Table\ActiveRow;


class Users extends Table
{
	/** @var string */
	protected $tableName = 'users';


	/**
	 * Finds one user by name or by e-mail.
	 *
	 * @param string user name or e-mail
	 * @return \Nette\Database\Table\ActiveRow|FALSE
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
	 * @parama array
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function register(array $values)
	{
		throw new \NetteAddons\DeprecatedException('This should not be used at all.');
		//return $this->createRow($values);
	}

	/**
	 * @param \Nette\Database\Table\ActiveRow
	 * @return \Nette\Security\Identity
	 */
	public function createIdentity(ActiveRow $user)
	{
		$data = $user->toArray();
		unset($data['password']);

		$role = strtolower($user->ref('users_groups', 'group_id')->g_title);

		return new Identity($user->id, $role, $data);
	}


	/**
	 * Returns selection of all addons authors.
	 *
	 * @return \Nette\Database\Table\Selection
	 */
	public function findAuthors()
	{
		$users = $this->db->table('addons')->select('DISTINCT(userId)');
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
		return $this->db->table('users')->insert(array(
			'id' => $id,
			'username' => $username,
			'realname' => $username,
			'password' => Authenticator::calculateHash($password),
			'group_id' => 4,
		));
	}
}
