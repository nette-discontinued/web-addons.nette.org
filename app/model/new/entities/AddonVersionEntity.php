<?php

namespace NetteAddons\Model;

use Nette\Utils\Validators;
use NetteAddons\Test\Model\AddonVersionEntityTest;

/**
 * @property-read string $composerFullName
 * @property-read string $version
 * @property-read string[]|array $licenses
 * @property-read AddonDependencyEntity[]|array $dependencies
 * @property-read string[]|array $suggest
 */
class AddonVersionEntity extends \Nette\Object
{
	/** @var string */
	private $composerFullName;
	/** @var string */
	private $version;
	/** @var string[]|array */
	private $licenses = array();
	/** @var AddonDependencyEntity[]|array */
	private $dependencies = array();
	/** @var string[]|array */
	private $suggest = array();

	/**
	 * @param string
	 * @param string
	 */
	public function __construct($composerFullName, $version)
	{
		Validators::assert($composerFullName, 'string', 'composerFullName');
		Validators::assert($composerFullName, 'pattern:(([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+))', 'composerFullName');
		Validators::assert($version, 'string', 'version');
		$this->composerFullName = $composerFullName;
		$this->version = $version;
	}

	/**
	 * @return string
	 */
	public function getComposerFullName()
	{
		return $this->composerFullName;
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @return string[]|array
	 */
	public function getLicenses()
	{
		return $this->licenses;
	}

	/**
	 * @param string
	 * @return AddonVersionEntity
	 */
	public function addLicense($license)
	{
		Validators::assert($license, 'string', 'license');
		$this->licenses[] = $license;
		sort($this->licenses);
		return $this;
	}

	/**
	 * @return AddonDependencyEntity[]|array
	 */
	public function getDependencies()
	{
		return $this->dependencies;
	}

	/**
	 * @param AddonDependencyEntity
	 * @return AddonVersionEntity
	 */
	public function addDependency(AddonDependencyEntity $dependency)
	{
		$this->dependencies[$dependency->getType() . '-' . $dependency->getDependencyName()] = $dependency;
		return $this;
	}

	/**
	 * @return string[]|array
	 */
	public function getSuggest()
	{
		return $this->suggest;
	}

	/**
	 * @param string
	 * @param string
	 * @return AddonVersionEntity
	 */
	public function addSuggest($name, $description)
	{
		Validators::assert($name, 'string', 'name');
		Validators::assert($name, 'pattern:(([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+))', 'name');
		Validators::assert($description, 'string', 'description');
		$this->suggest[$name] = $description;
		return $this;
	}
}
