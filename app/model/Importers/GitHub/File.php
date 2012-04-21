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
	 * Call remote API
	 *
	 * @param string
	 * @return string
	 */
	protected function exec($path)
	{
		$url = new \Nette\Http\Url($this->baseUrl);
		$url->setPath($path);
		return $this->curl->get($url);
	}

	/**
	 * @param string
	 * @param string
	 */
	public function get($hash, $path)
	{
		try {
			return $this->exec("/{$this->vendor}/{$this->name}/{$hash}/$path");
		} catch(\NetteAddons\InvalidStateException $e) {
			if ($e->getCode() == 404) {
				return NULL;
			}
			throw $e;
		}
	}
}
