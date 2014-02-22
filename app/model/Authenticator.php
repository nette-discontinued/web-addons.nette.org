<?php

namespace NetteAddons\Model;

use Nette\Utils\Strings;
use Nette\Security\IAuthenticator;
use NetteAddons\Utils\HttpStreamRequestFactory;


class Authenticator extends \Nette\Object implements IAuthenticator
{
	const EXTERNAL_URL = 'http://forum.nette.org/cs/login.php?action=in';

	/** @var \NetteAddons\Model\Users */
	private $users;

	/** @var \NetteAddons\Utils\HttpStreamRequestFactory */
	private $requestFactory;


	public function __construct(Users $users, HttpStreamRequestFactory $requestFactory)
	{
		$this->users = $users;
		$this->requestFactory = $requestFactory;
	}


	/**
	 * Performs an authentication
	 *
	 * @param array
	 * @return \Nette\Security\Identity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$user = $this->users->findOneByName($username);

		if (!$user) {
			if (!$user = $this->authenticateExternal($username, $password, TRUE)) {
				throw new \Nette\Security\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
			}
		}

		if (strlen($user->password) === 0 && $this->authenticateExternal($username, $password, FALSE)) {
			$user->update(array(
				'password' => $this->calculateHash($password),
			));
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


	/**
	 * Authenticate again external site (hack ;)
	 *
	 * @todo remove before release
	 *
	 * @param string
	 * @param string
	 * @param bool
	 * @return \Nette\Database\Table\ActiveRow|bool
	 */
	private function authenticateExternal($username, $password, $create = FALSE)
	{
		$curl = curl_init(self::EXTERNAL_URL);
		curl_setopt_array($curl, array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => array(
				'form_sent' => 1,
				'req_name' => $username,
				'req_password' => $password,
				'redirect_url' => 'index.php',
			),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_COOKIEFILE => '',
			CURLOPT_FOLLOWLOCATION => true,
		));

		$html = curl_exec($curl);

		if (!$match = Strings::match($html, '~<a href="profile\.php\?id=(\d+)" title=~')) {
			return FALSE;
		}

		if ($create) {
			$id = $match[1];
			return $this->users->createUser($id, $username, $password);
		} else {
			return TRUE;
		}
	}
}
