<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @author	Patrik Votoček
 */
class RepositoryImporter extends Nette\Object implements IAddonImporter
{
	/**
	 * @var \NetteAddons\Model\GitHub\Repository
	 */
	private $loader;

	/**
	 * @var string
	 */
	private $url;



	/**
	 * @param callable
	 * @param string
	 */
	public function __construct($repositoryFactory, $url)
	{
		$this->url = $url;
		$this->loader = callback($repositoryFactory)->invoke($url);
	}



	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
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
