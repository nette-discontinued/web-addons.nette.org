<?php

namespace NetteAddons\Model;

use Nette\Utils\Strings;
use Nette\Security\IAuthenticator;


class Authenticator extends \Nette\Object implements IAuthenticator
{
	/** @var \NetteAddons\Model\Users */
	private $users;


	public function __construct(Users $users)
	{
		$this->users = $users;
	}


	/**
	 * @param array
	 * @return \Nette\Security\Identity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$user = $this->users->findOneByName($username);

		if (!$user) {
			throw new \Nette\Security\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
		}

		if ($user->password !== $this->calculateHash($password, $user->password)) {
			throw new \Nette\Security\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}

		if (empty($user->apiToken)) {
			$user->update(array(
				'apiToken' => Strings::random(),
			));
		}

		return $this->users->createIdentity($user);
	}


	/**
	 * Computes password hash.
	 *
	 * @param string
	 * @param string|NULL
	 * @return string
	 */
	public static function calculateHash($password, $salt = NULL)
	{
		if ($password === Strings::upper($password)) { // perhaps caps lock is on
			$password = Strings::lower($password);
		}
		return crypt($password, $salt ?: '$2a$07$' . Strings::random(22));
	}
}
