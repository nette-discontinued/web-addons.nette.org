<?php

namespace NetteAddons\Utils;


use Nette\Utils\Strings;

/**
 * @author Michael Moravec
 * @author Patrik Votoček
 *
 * @property-read array $headers
 */
class CurlRequest extends \Nette\FreezableObject
{
	/** regexp's for parsing */
	const HEADER_REGEXP = '~(?P<header>.*?)\:\s(?P<value>.*)~',
		VERSION_AND_STATUS = '~HTTP/(?P<version>\d\.\d)\s(?P<code>\d\d\d)\s(?P<status>.*)~',
		CONTENT_TYPE = '~^(?P<type>[^;]+);[\t ]*charset=(?P<charset>.+)$~i';

	/** @var array */
	private $options = array();

	/** @var array */
	private $headers = array();



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
	 * @param  int
	 * @param  mixed
	 * @return CurlRequest
	 */
	public function setOption($name, $value)
	{
		$this->updating();

		$this->options[$name] = $value;
		return $this;
	}



	/**
	 * @param  int
	 * @return CurlRequest
	 */
	public function removeOption($name)
	{
		$this->updating();

		unset($this->options[$name]);
		return $this;
	}



	/**
	 * @param string
	 */
	private function parseHeader($headerString)
	{
		$headers = Strings::split($headerString, "~[\n\r]+~", PREG_SPLIT_NO_EMPTY);

		// Extract the version and status from the first header
		$versionAndStatus = array_shift($headers);
		$matches = Strings::match($versionAndStatus, self::VERSION_AND_STATUS);
		if (count($matches) > 0) {
			$this->headers['Http-Version'] = $matches['version'];
			$this->headers['Status-Code'] = $matches['code'];
			$this->headers['Status'] = $matches['code'].' '.$matches['status'];
		}

		// Convert headers into an associative array
		foreach ($headers as $header) {
			$matches = Strings::match($header, self::HEADER_REGEXP);
			$this->headers[$matches['header']] = $matches['value'];
		}
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

		curl_setopt($ch, CURLOPT_HEADER, TRUE);

		$data = curl_exec($ch);

		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$this->parseHeader(substr($data, 0, $headerSize));
		$body = substr($data, $headerSize);

		if (($err = curl_errno($ch)) !== CURLE_OK || $data === FALSE) {
			if ($err !== CURLE_HTTP_NOT_FOUND) { // correct name is CURLE_HTTP_RETURNED_ERROR
				$e = new \NetteAddons\Utils\CurlException(curl_error($ch), $err);
				curl_close($ch);
				throw $e;
			}
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode !== 200 || $err === CURLE_HTTP_NOT_FOUND) {
			curl_close($ch);
			throw new \NetteAddons\Utils\HttpException("Server returned HTTP code other than 200 OK.", (int) $httpCode);
		}

		curl_close($ch);

		return $body;
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



	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
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
