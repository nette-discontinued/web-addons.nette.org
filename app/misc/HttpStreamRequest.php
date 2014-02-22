<?php

namespace NetteAddons\Utils;

use Nette\Utils\Strings;


/**
 * @property-read array $headers
 */
class HttpStreamRequest extends \Nette\FreezableObject
{
	/** regexp's for parsing */
	const HEADER_REGEXP = '~(?P<header>.*?)\:\s(?P<value>.*)~',
		VERSION_AND_STATUS = '~HTTP/(?P<version>\d\.\d)\s(?P<code>\d\d\d)\s(?P<status>.*)~',
		CONTENT_TYPE = '~^(?P<type>[^;]+);[\t ]*charset=(?P<charset>.+)$~i';

	/** @var array */
	private $options;

	/** @var string */
	private $url;

	/** @var array */
	private $headers = array();


	/**
	 * @param string
	 */
	public function __construct($url)
	{
		$this->url = $url;
		$this->options = array(
			'ignore_errors' => 1,
		);
	}


	/**
	 * @param string
	 * @param mixed
	 * @return HttpStreamRequest
	 */
	public function setOption($name, $value)
	{
		$this->updating();
		$this->options[$name] = $value;
		return $this;
	}


	/**
	 * @param int
	 * @return HttpStreamRequest
	 */
	public function removeOption($name)
	{
		$this->updating();
		unset($this->options[$name]);
		return $this;
	}


	/**
	 * @param string
	 * @param string
	 * @return HttpStreamRequest
	 */
	public function addHeader($name, $value)
	{
		$this->updating();
		$this->options['headers'][] = "$name: $value";
		return $this;
	}


	/**
	 * @param string[]|array
	 */
	private function parseHeaders(array $headers)
	{
		$headers = array_reverse($headers);
		foreach ($headers as $header) {
			if ($matches = Strings::match($header, self::VERSION_AND_STATUS)) {
				$this->headers['Http-Version'] = $matches['version'];
				$this->headers['Status-Code'] = (int) $matches['code'];
				$this->headers['Status'] = $matches['code'] . ' ' . $matches['status'];
				break;

			} elseif ($matches = Strings::match($header, self::HEADER_REGEXP)) {
				$this->headers[$matches['header']] = $matches['value'];
			}
		}
	}


	/**
	 * @return string page content
	 * @throws \NetteAddons\Utils\HttpException if server returns HTTP code other than 200 OK
	 * @throws \NetteAddons\Utils\StreamException if something else fails
	 */
	public function execute()
	{
		$this->freeze();

		$context = stream_context_create(array('http' => $this->options));
		$handle = @fopen($this->url, 'r', FALSE, $context);
		if (!$handle) {
			$err = error_get_last();
			throw new \NetteAddons\Utils\StreamException($err['message']);
		}

		$meta = stream_get_meta_data($handle);
		$this->parseHeaders($meta['wrapper_data']);

		if (!isset($this->headers['Status-Code'])) {
			throw new \NetteAddons\Utils\StreamException('Invalid HTTP response');
		}

		if ($this->headers['Status-Code'] !== 200) {
			throw new \NetteAddons\Utils\HttpException("Server returned HTTP code other than 200 OK.", $this->headers['Status-Code']);
		}

		$content = stream_get_contents($handle);
		if (!is_string($content)) {
			throw new \NetteAddons\Utils\StreamException('Failed to read stream content.');
		}

		return $content;
	}


	/******* shortcuts ********/


	/**
	 * @param int timeout in milliseconds
	 * @return HttpStreamRequest
	 */
	public function setTimeout($timeout)
	{
		$this->setOption('timeout', $timeout * 1000);
		return $this;
	}


	/**
	 * @param string valid HTTP 1.0 method
	 * @return HttpStreamRequest
	 */
	public function setMethod($method)
	{
		$this->setOption('method', $method);
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

class StreamException extends \RuntimeException
{

}

class HttpException extends \NetteAddons\IOException
{

}
