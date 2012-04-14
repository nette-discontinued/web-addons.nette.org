<?php

namespace NetteAddons\Model\GitHub;

class Repository extends \Nette\Object
{
	/** @var Service */
	private $service;
	/** @var string */
	private $vendor;
	/** @var string */
	private $name;

	/**
	 * @param Service
	 */
	public function __construct(Service $service, $vendor, $name)
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
}
