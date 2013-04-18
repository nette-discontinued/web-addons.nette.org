<?php

namespace NetteAddons\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security as NS,
	NetteAddons\Utils\HttpStreamRequestFactory;



/**
 * Users authenticator.
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator
{
	const EXTERNAL_URL = 'http://forum.nette.org/cs/login.php?action=in';

	/** @var Users */
	private $users;

	/** @var \NetteAddons\Utils\HttpStreamRequestFactory */
	private $requestFactory;



	/**
	 * @param  Users
	 * @param  \NetteAddons\Utils\HttpStreamRequestFactory
	 */
	public function __construct(Users $users, HttpStreamRequestFactory $requestFactory)
	{
		$this->users = $users;
		$this->requestFactory = $requestFactory;
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
			if (!$user = $this->authenticateExternal($username, $password)) {
				throw new NS\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
			}
		}

		// password migration
		if (strlen($user->password) === 40 && $user->password === sha1($password)) {
			$user->password = $this->calculateHash($password);
			$user->update();
		}

		if ($user->password !== $this->calculateHash($password, $user->password)) {
			throw new NS\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
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
	 * @param  string
	 * @param  string|NULL
	 * @return string
	 */
	public static function calculateHash($password, $salt = NULL)
	{
		if ($password === Strings::upper($password)) { // perhaps caps lock is on
			$password = Strings::lower($password);
		}
		return crypt($password, $salt ?: '$2a$07$' . Strings::random(22));
	}



	/**
	 * Authenticate again external site (hack ;)
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	private function authenticateExternal($username, $password)
	{
		$req = $this->requestFactory->create(self::EXTERNAL_URL);
		$req->setMethod('POST');
		$req->setOption('content', http_build_query(array(
			'form_sent' => 1,
			'req_name' => $username,
			'req_password' => $password,
			'redirect_url' => 'index.php',
		)));

		try {
			$html = $req->execute();
		} catch(\NetteAddons\Utils\HttpException $e) { // auth failure
			return FALSE;
		}

		if (!$match = Strings::match($html, '~<a href="profile\.php\?id=(\d+)" title=~')) {
			return FALSE;
		}
		$id = $match[1];

		return $this->users->createUser($id, $username, $password);
	}

}
