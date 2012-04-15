<?php

namespace NetteAddons;

/**
 * @author	Patrik Votoček
 */
class Curl extends \Nette\Object
{
	const UA = 'Nette Addons';

	/** @var int */
	private $timeout;

	/**
	 * @param int
	 */
	public function __construct($timeout = 500)
	{
		if (!extension_loaded('curl')) {
			throw new \NetteAddons\InvalidStateException('Missing cURL extension');
		}

		$this->timeout = $timeout;
	}

	/**
	 * @return resource
	 */
	protected function create()
	{
		if (!defined('CURLOPT_TIMEOUT_MS')) {
			define('CURLOPT_TIMEOUT_MS', 155);
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_USERAGENT, self::UA);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);

		if (PHP_OS == 'WINNT') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		}

		return $ch;
	}

	/**
	 * @param string|\Nette\Http\Url
	 * @return string
	 * @throws InvalidStateException
	 */
	public function get($url)
	{
		$ch = $this->create();

		curl_setopt($ch, CURLOPT_URL, (string)$url);
		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);

		$data = curl_exec($ch);

		if ($err = curl_errno($ch) != 0) {
			throw new \NetteAddons\InvalidStateException("cURL error #$err ".curl_error($ch));
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode != 200) {
			throw new \NetteAddons\InvalidStateException("Server error", $httpCode);
		}

		curl_close($ch);

		return $data;
	}
}
