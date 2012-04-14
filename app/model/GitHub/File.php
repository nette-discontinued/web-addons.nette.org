<?php

namespace NetteAddons\Model\GitHub;

/**
 * @author	Patrik Votoček
 */
class File extends \Nette\Object
{
	/** @var Service */
	private $service;
	/** @var string */
	private $vendor;
	/** @var string */
	private $name;
	/** @var string */
	private $commit;

	/**
	 * @param BlobService
	 * @param string
	 * @param string
	 * @param string
	 */
	public function __construct(BlobService $service, $vendor, $name, $commit)
	{
		$this->service = $service;
		$this->vendor = $vendor;
		$this->name = $name;
		$this->commit = $commit;
	}

	/**
	 * @return array
	 */
	public function get($path)
	{
		try {
			return $this->service->exec("/{$this->vendor}/{$this->name}/{$this->commit}/$path");
		} catch(\NetteAddons\InvalidStateException $e) {
			if ($e->getCode() == 404) {
				throw new \NetteAddons\Model\GitHub\FileNotFoundException($e->getMessage(), $e->getCode(), $e);
			}

			throw $e;
		}
	}
}
