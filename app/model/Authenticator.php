<?php

namespace NetteAddons\Model;

use Nette\Object;
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
	 *
	 * @return \Nette\Security\Identity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$user = $this->users->findByName($username);

		if (!$user) {
			throw new NS\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($user->password !== $this->calculateHash($password)) {
			throw new NS\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}

		return $this->users->createIdentity($user);
	}



	/**
	 * Computes salted password hash.
	 *
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
		return md5($password . str_repeat('*enter any random salt here*', 10));
	}

}
