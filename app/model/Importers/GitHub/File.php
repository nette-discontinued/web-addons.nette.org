<?php

namespace NetteAddons\Model\Importers\GitHub;



/**
 * @author	Patrik Votoček
 */
class File extends \Nette\Object
{
	/** @var \NetteAddons\Curl */
	private $curl;

	/** @var string */
	private $vendor;

	/** @var string */
	private $name;

	/** @var string */
	public $baseUrl = 'https://raw.github.com';

	/**
	 * @param \NetteAddons\Curl
	 * @param string
	 * @param string
	 */
	public function __construct(\NetteAddons\Curl $curl, $vendor, $name)
	{
		$this->curl = $curl;
		$this->vendor = $vendor;
		$this->name = $name;
	}

	/**
	 * Calls remote API.
	 *
	 * @param  string
	 * @return string
	 * @throws \NetteAddons\IOException
	 */
	protected function exec($path)
	{
		try {
			$url = new \Nette\Http\Url($this->baseUrl);
			$url->setPath($path);
			return $this->curl->get($url);

		} catch (\NetteAddons\CurlException $e) {
			throw new \NetteAddons\IOException('cURL execution failed.', NULL, $e);

		} catch (\NetteAddons\InvalidStateException $e) {
			throw new \NetteAddons\IOException();
		}
	}

	/**
	 * Gets raw content of specified file.
	 *
	 * @param  string commit sha-1 hash
	 * @param  string file path
	 * @return string file raw content
	 * @throws \NetteAddons\IOException
	 */
	public function get($hash, $path)
	{
		return $this->exec("/{$this->vendor}/{$this->name}/{$hash}/$path");
	}
}
