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
	 * @param int The maximum number of milliseconds to allow cURL functions to execute.
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
		curl_setopt($ch, CURLOPT_FAILONERROR, FALSE);

		if (PHP_OS === 'WINNT') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		}

		return $ch;
	}



	/**
	 * @param  string|\Nette\Http\Url
	 * @return string
	 * @throws CurlException if cURL execution fails, see http://curl.haxx.se/libcurl/c/libcurl-errors.html
	 * @throws HttpException if server returns HTTP code other than 200 OK
	 */
	public function get($url)
	{
		$ch = $this->create();

		curl_setopt($ch, CURLOPT_URL, (string) $url);
		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);

		$data = curl_exec($ch);
		if (($err = curl_errno($ch)) !== CURLE_OK || $data === FALSE) {
			if ($err !== CURLE_HTTP_NOT_FOUND) { // correct name is CURLE_HTTP_RETURNED_ERROR
				throw new \NetteAddons\CurlException(curl_error($ch), $err);
			}
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode !== 200 || $err === CURLE_HTTP_NOT_FOUND) {
			throw new \NetteAddons\HttpException("Server returned HTTP code other than 200 OK.", (int) $httpCode);
		}

		curl_close($ch);

		return $data;
	}
}



/**
 * @link http://curl.haxx.se/libcurl/c/libcurl-errors.html
 */
class CurlException extends \RuntimeException
{

}

class HttpException extends \NetteAddons\IOException
{

}
