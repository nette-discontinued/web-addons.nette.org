<?php

namespace NetteAddons;

/**
 * @author Michael Moravec
 */
class CurlRequest extends \Nette\FreezableObject
{
	/** @var array */
	private $options = array();



	public function __construct($url = NULL)
	{
		if (!extension_loaded('curl')) {
			throw new \NetteAddons\InvalidStateException('Missing cURL extension');
		}

		if ($url !== NULL) {
			$this->setOption(CURLOPT_URL, (string) $url);
		}
	}



	/**
	 * @param int $name
	 * @param mixed $value
	 * @return CurlRequest
	 */
	public function setOption($name, $value)
	{
		$this->updating();

		$this->options[$name] = $value;
		return $this;
	}



	/**
	 * @param int $name
	 * @return CurlRequest
	 */
	public function removeOption($name)
	{
		$this->updating();

		unset($this->options[$name]);
		return $this;
	}



	/**
	 * @return string
	 * @throws CurlException if cURL execution fails, see http://curl.haxx.se/libcurl/c/libcurl-errors.html
	 * @throws HttpException if server returns HTTP code other than 200 OK
	 */
	public function execute()
	{
		$this->freeze();

		$ch = curl_init();
		curl_setopt_array($ch, $this->options);
		$data = curl_exec($ch);

		if (($err = curl_errno($ch)) !== CURLE_OK || $data === FALSE) {
			if ($err !== CURLE_HTTP_NOT_FOUND) {// correct name is CURLE_HTTP_RETURNED_ERROR
				$e = new \NetteAddons\CurlException(curl_error($ch), $err);
				curl_close($ch);
				throw $e;
			}
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode !== 200 || $err === CURLE_HTTP_NOT_FOUND) {
			curl_close($ch);
			throw new \NetteAddons\HttpException("Server returned HTTP code other than 200 OK.", (int) $httpCode);
		}

		curl_close($ch);

		return $data;
	}



	/******* shortcuts ********/



	/**
	 * @param int $timeout timeout in milliseconds
	 * @return CurlRequest
	 */
	public function setTimeout($timeout)
	{
		$this->setOption(CURLOPT_TIMEOUT_MS, $timeout);
		return $this;
	}



	/**
	 * @param string $method valid HTTP 1.0 method
	 * @return CurlRequest
	 */
	public function setMethod($method)
	{
		$this->removeOption(CURLOPT_HTTPGET);
		$this->setOption(CURLOPT_CUSTOMREQUEST, $method);

		return $this;
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
