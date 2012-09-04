<?php

namespace NetteAddons\Model;

use Nette\Object;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection as TableSelection;
use Nette\Security\Identity;



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
			->where('name = ? OR email = ?', $name, $name)
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
}
