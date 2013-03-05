<?php

namespace NetteAddons\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Database\SqlLiteral,
	Nette\Database\Table\ActiveRow,
	Nette\Security as NS,
	NetteAddons\Utils\CurlRequestFactory;



/**
 * Users authenticator.
 */
class Authenticator extends Nette\Object implements NS\IAuthenticator
{
	const EXTERNAL_URL = 'http://forum.nette.org/cs/login.php?action=in';

	/** @var Users */
	private $users;

	/** @var \NetteAddons\Utils\CurlRequestFactory */
	private $curlFactory;



	/**
	 * @param  Users
	 * @param  \NetteAddons\Utils\CurlRequestFactory
	 */
	public function __construct(Users $users, CurlRequestFactory $curlFactory)
	{
		$this->users = $users;
		$this->curlFactory = $curlFactory;
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

		if ($user->password !== $this->calculateHash($password)) {
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
	 * @return string
	 */
	public function calculateHash($password)
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
		$req = $this->curlFactory->create(self::EXTERNAL_URL);
		$req->setOption(CURLOPT_POST, TRUE);
		$req->setOption(CURLOPT_POSTFIELDS, http_build_query(array(
			'form_sent' => 1,
			'req_name' => $username,
			'req_password' => $password,
			'redirect_url' => 'index.php',
		)));
		$req->setOption(CURLOPT_COOKIEFILE, ''); // needs to be here to store cookies between redirects

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
