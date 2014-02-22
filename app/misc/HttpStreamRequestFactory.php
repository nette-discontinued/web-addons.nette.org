<?php

namespace NetteAddons\Utils;

use NetteAddons\Portal;


class HttpStreamRequestFactory extends \Nette\Object
{
	const UA = 'Nette Addons';

	/** @var int */
	private $timeout;


	/**
	 * @param int read timeout in milisecond
	 */
	public function __construct($timeout = 0)
	{
		$this->timeout = $timeout;
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
	 * @param string|NULL
	 * @return HttpStreamRequest
	 */
	public function create($url = NULL)
	{
		$request = new HttpStreamRequest($url);
		$request->setOption('user_agent', static::getUAString());
		$request->setOption('follow_location', 1);

		if ($this->timeout) {
			$request->setTimeout($this->timeout);
		}

		return $request;
	}
}
