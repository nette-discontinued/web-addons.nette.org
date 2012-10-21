<?php

namespace NetteAddons\Utils;

use NetteAddons\Portal;


/**
 * @author Patrik Votoček
 * @author Michael Moravec
 */
class CurlRequestFactory extends \Nette\Object
{
	const UA = 'Nette Addons';

	/** @var int */
	private $timeout;



	/**
	 * @param int The maximum number of milliseconds to allow cURL functions to execute.
	 */
	public function __construct($timeout = 0)
	{
		if (!extension_loaded('curl')) {
			throw new \NetteAddons\InvalidStateException('Missing cURL extension');
		}

		$this->timeout = $timeout;

		if (!defined('CURLOPT_TIMEOUT_MS')) {
			define('CURLOPT_TIMEOUT_MS', 155);
		}
	}



	/**
	 * @return string
	 */
	public static function getUAString()
	{
		return sprintf(
			static::UA . '/%s (%s; %s; PHP %s.%s.%s)',
			Portal::VERSION,
			php_uname('s'),
			php_uname('r'),
			PHP_MAJOR_VERSION,
			PHP_MINOR_VERSION,
			PHP_RELEASE_VERSION
		);
	}



	/**
	 * @return CurlRequest
	 */
	public function create($url = NULL)
	{
		$request = new CurlRequest($url);

		$request->setOption(CURLOPT_USERAGENT, static::getUAString());
		$request->setOption(CURLOPT_FOLLOWLOCATION, TRUE);
		$request->setOption(CURLOPT_RETURNTRANSFER, TRUE);
		$request->setOption(CURLOPT_FAILONERROR, FALSE);

		if (PHP_OS === 'WINNT') {
			$request->setOption(CURLOPT_SSL_VERIFYPEER, FALSE);
		}

		if ($this->timeout) {
			$request->setTimeout($this->timeout);
		}

		return $request;
	}
}
