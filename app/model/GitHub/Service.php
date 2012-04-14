<?php

namespace NetteAddons\Model\GitHub;

/**
 * @author	Patrik Votoček
 */
class Service extends \Nette\Object
{
	const UA = 'Nette Addons';

	const METHOD_GET = 'GET';

	/** @var string */
	private $baseUrl;
	/** @var int */
	private $timeout;

	/**
	 * @param string
	 * @param int timeout in ms
	 */
	public function __construct($baseUrl = 'https://api.github.com', $timeout = 50000)
	{
		if (!extension_loaded('curl')) {
			throw new \NetteAddons\InvalidStateException('Missing cURL extension');
		}

		$this->baseUrl = $baseUrl;
		$this->timeout = $timeout;
	}

	/**
	 * @param string|\Nette\Http\Url
	 * @return resource
	 */
	protected function createCurl($url)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, (string)$url);
		curl_setopt($ch, CURLOPT_USERAGENT, self::UA);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);

		return $ch;
	}

	/**
	 * @param string
	 * @return stdClass|NULL
	 */
	protected function responseToJson($input)
	{
		$output = json_decode($input);

		if ($output === NULL) {
			throw new \NetteAddons\InvalidStateException("Invalid JSON");
		}

		return $output;
	}

	/**
	 * @param string
	 * @param string
	 * @return stdClass|NULL
	 * @throws \NetteAddons\NotImplementedException
	 */
	public function exec($path, $method = self::METHOD_GET)
	{
		$url = new \Nette\Http\Url($this->baseUrl);
		$url->setPath($path);

		if ($method != self::METHOD_GET) {
			throw new \NetteAddons\NotImplementedException;
		}

		$ch = $this->createCurl($url);

		$data = curl_exec($ch);

		if ($err = curl_errno($ch) != 0) {
			throw new \NetteAddons\InvalidStateException("cURL error #$err ".curl_error($ch));
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode != 200) {
			throw new \NetteAddons\InvalidStateException("GitHub returns $httpCode code");
		}

		curl_close($ch);

		return $this->responseToJson($data);
	}
}