<?php

namespace NetteAddons\Model;

use Guzzle\Http\Client;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Http\Url;
use Nette\Security\Identity;
use Nette\Utils\Json;
use Nette\Utils\Strings;


class NetteOrgAuthenticator extends \Nette\Object implements \Nette\Security\IAuthenticator
{
	/** @var string */
	private $cryptPassword;

	/** @var Url */
	private $baseUrl;

	/** @var Client */
	private $httpClient;

	/** @var \Nette\Database\Context */
	private $db;


	/**
	 * @param string
	 * @param \Nette\Database\Context
	 */
	public function __construct($cryptPassword, Context $db)
	{
		$this->cryptPassword = $cryptPassword;
		$this->baseUrl = new Url('http://nette.org/loginpoint.php');
		$this->httpClient = new Client;
		$this->httpClient->setUserAgent('Nette Addons portal authenticator');

		$this->db = $db;
	}


	public function authenticate(array $credentials)
	{
		$mcrypt = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($mcrypt), MCRYPT_DEV_RANDOM);
		mcrypt_generic_init($mcrypt, $this->cryptPassword, $iv);

		$url = $this->getUrl($credentials[self::USERNAME], $credentials[self::PASSWORD], $mcrypt, $iv);

		try {
			$res = $this->httpClient->get($url)->send();
		} catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
			if ($e->getResponse()->getStatusCode() === 403) {
				throw new \Nette\Security\AuthenticationException("User '{$credentials[self::USERNAME]}' not found.", self::INVALID_CREDENTIAL);
			} elseif ($e->getResponse()->getStatusCode() === 404) {
				throw new \Nette\Security\AuthenticationException("Invalid password.", self::IDENTITY_NOT_FOUND);
			} else {
				throw $e;
			}
		}

		$responseBody = trim(mdecrypt_generic($mcrypt, $res->getBody(TRUE)));
		$apiData = Json::decode($responseBody);

		$user = $this->db->table('users')->where('id = ?', $apiData->id)->fetch();

		$registered = new \DateTimeImmutable($apiData->registered->date, new \DateTimeZone($apiData->registered->timezone));
		$userData = array(
			'username' => $credentials[self::USERNAME],
			'password' => $this->calculateAddonsPortalPasswordHash($credentials[self::PASSWORD]),
			'email' => $apiData->email,
			'realname' => $apiData->realname,
			'url' => $apiData->url,
			'signature' => $apiData->signature,
			'language' => $apiData->language,
			'num_posts' => $apiData->num_posts,
			'apiToken' => $apiData->apiToken,
			'registered' => $registered->getTimestamp(),
		);

		if (!$user) {
			$userData['id'] = $apiData->id;
			$userData['group_id'] = 4;
			$this->db->table('users')->insert($userData);
			$user = $this->db->table('users')->where('username = ?', $credentials[self::USERNAME])->fetch();
		} else {
			$user->update($userData);
		}

		return $this->createIdentity($user);
	}


	private function getUrl($username, $password, $mcrypt, $iv)
	{
		$encrypted = mcrypt_generic($mcrypt, $password);

		$url = clone $this->baseUrl;
		$url->setQueryParameter('name', $username);
		$url->setQueryParameter('password', base64_encode($encrypted));
		$url->setQueryParameter('iv', base64_encode($iv));

		return $url;
	}


	/**
	 * @param string
	 * @param string|NULL
	 * @return string
	 */
	private function calculateAddonsPortalPasswordHash($password, $salt = NULL)
	{
		if ($password === Strings::upper($password)) { // perhaps caps lock is on
			$password = Strings::lower($password);
		}
		return crypt($password, $salt ?: '$2a$07$' . Strings::random(22));
	}


	/**
	 * @param \Nette\Database\Table\ActiveRow
	 * @return \Nette\Security\Identity
	 */
	private function createIdentity(ActiveRow $user)
	{
		$data = $user->toArray();
		unset($data['password']);

		$role = strtolower($user->ref('users_groups', 'group_id')->g_title);

		return new Identity($user->id, $role, $data);
	}
}
