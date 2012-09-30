<?php

namespace NetteAddons\Model;

use Nette\Object;
use Nette\Utils\Strings;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;
use Nette\Security as NS;



/**
 * Users authenticator.
 */
class Authenticator extends Object implements NS\IAuthenticator
{
	/** @var Users */
	private $users;



	/**
	 * @param Users $users
	 */
	public function __construct(Users $users)
	{
		$this->users = $users;
	}



	/**
	 * Performs an authentication
	 *
	 * @param  array
	 * @return \Nette\Security\Identity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$user = $this->users->findOneByName($username);

		if (!$user) {
			throw new NS\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($user->password !== $this->calculateHash($password)) {
			throw new NS\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}

		if (empty($user->created)) {
			$this->onFirstLogin($user);
		}

		return $this->users->createIdentity($user);
	}



	/**
	 * Computes password hash.
	 *
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
		return sha1($password);
	}



	/**
	 * Called when user logs in to the portal for the first time, so that we can initialize some columns
	 * @param  ActiveRow
	 */
	private function onFirstLogin(ActiveRow $user)
	{
		$user->getTable()->getConnection()->table('users_details')->insert(array(
			'id' => $user->id,
			'created' => new SqlLiteral('NOW()'),
			'apiToken' => Strings::random(),
		));
	}

}
