<?php

namespace NetteAddons\Model\GitHub;

use Nette\Utils\Strings;

/**
 * @author	Patrik Votoček
 */
class Repository extends \Nette\Object
{
	/** @var ApiService */
	private $service;
	/** @var string */
	private $vendor;
	/** @var string */
	private $name;

	/**
	 * @param ApiService
	 * @param string
	 * @param string
	 */
	public function __construct(ApiService $service, $vendor, $name)
	{
		$this->service = $service;
		$this->vendor = $vendor;
		$this->name = $name;
	}

	/**
	 * @return array
	 */
	public function getTags()
	{
		$data = $this->service->exec("/repos/{$this->vendor}/{$this->name}/tags") ?: array();
		$tags = array();
		foreach ($data as $tag) {
			$tags[$tag->name] = $tag->commit->sha;
		}
		return $tags;
	}

	/**
	 * @return array
	 */
	public function getBranches()
	{
		$data = $this->service->exec("/repos/{$this->vendor}/{$this->name}/branches") ?: array();
		$branches = array();
		foreach ($data as $branche) {
			$branches[$branche->name] = $branche->commit->sha;
		}
		return $branches;
	}

	/**
	 * @param ApiService
	 * @param string
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public static function createFromUrl(ApiService $service, $url)
	{
		if (!Strings::startsWith($url, 'http://github.com/') && Strings::startsWith($url, 'https://github.com/')) {
			throw new \NetteAddons\InvalidArgumentException("Invalid github url");
		}

		$url = new \Nette\Http\Url($url);
		list($vendor, $name) = explode('/', substr($url->getPath(), 1));
		return new static($service, $vendor, $name);
	}
}
