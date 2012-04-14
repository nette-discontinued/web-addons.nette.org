<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @author	Patrik Votoček
 */
class RepositoryImporter extends Nette\Object implements IAddonImporter
{
	/** @var callable */
	private $loader;

	/**
	 * @param callable
	 * @param string
	 */
	public function __construct($repostiryFactory, $url)
	{
		$this->loader = callback($this->repostiryFactory)->invoke($url);
	}

	/**
	 * @return Addon
	 */
	public function import()
	{
		return $this->loader->getMainMetadata();
	}

	/**
	 * @return AddonVersion[]
	 */
	public function importVersions()
	{
		return $this->loader->getVersionsMetadatas();
	}
}
