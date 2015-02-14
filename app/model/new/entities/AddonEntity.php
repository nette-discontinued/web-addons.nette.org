<?php

namespace NetteAddons\Model;

use Nette\Utils\Strings;
use Nette\Utils\Validators;

/**
 * @property-read string $composerFullName
 * @property-read string $composerVendor
 * @property-read string $composerName
 * @property-read string $perex
 * @property-read AddonVersionEntity[]|array $versions
 * @property-read string|NULL $github
 * @property-read string|NULL $packagist
 */
class AddonEntity extends \Nette\Object
{
	const COMPOSER_NAME_REGEXP = '((?P<vendor>[a-zA-Z0-9]+(?:(?:-|_)[a-zA-Z0-9]+)*)/(?P<name>[a-zA-Z0-9]+(?:(?:-|_)[a-zA-Z0-9]+)*))';

	/** @var string */
	private $composerFullName;
	/** @var string */
	private $perex = '';
	/** @var AddonVersionEntity[]|array */
	private $versions = array();
	/** @var string|NULL */
	private $github;
	/** @var string|NULL */
	private $packagist;
	/** @var integer */
	private $stars = 0;

	/**
	 * @param string
	 */
	public function __construct($composerFullName)
	{
		Validators::assert($composerFullName, 'string', 'composerFullName');
		Validators::assert($composerFullName, 'pattern:(([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+))', 'composerFullName');
		$this->composerFullName = $composerFullName;
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
	 * @throws \Nette\Utils\RegexpException
	 */
	public function getComposerVendor()
	{
		$data = Strings::match($this->composerFullName, static::COMPOSER_NAME_REGEXP);
		return $data['vendor'];
	}

	/**
	 * @return string
	 * @throws \Nette\Utils\RegexpException
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function getComposerName()
	{
		$data = Strings::match($this->composerFullName, static::COMPOSER_NAME_REGEXP);
		return $data['name'];
	}

	/**
	 * @return string
	 */
	public function getPerex()
	{
		return $this->perex;
	}

	/**
	 * @param string
	 * @return AddonEntity
	 * @throws \Nette\Utils\AssertionException
	 */
	public function setPerex($perex)
	{
		Validators::assert($perex, 'string', 'perex');
		$this->perex = $perex;
		return $this;
	}

	/**
	 * @return AddonVersionEntity[]|array
	 */
	public function getVersions()
	{
		return $this->versions;
	}

	/**
	 * @param AddonVersionEntity
	 * @return AddonEntity
	 */
	public function addVersion(AddonVersionEntity $version)
	{
		$this->versions[$version->getVersion()] = $version;
		return $this;
	}

	/**
	 * @return string|NULL
	 */
	public function getGithub()
	{
		return $this->github;
	}

	/**
	 * @param string
	 * @return AddonEntity
	 */
	public function setGithub($github)
	{
		Validators::assert($github, 'string', 'github');
		UrlsHelper::assertGithubRepositoryUrl($github);
		$this->github = UrlsHelper::normalizeGithubRepositoryUrl($github);
		return $this;
	}

	/**
	 * @return string|NULL
	 */
	public function getPackagist()
	{
		return $this->packagist;
	}

	/**
	 * @param string
	 * @return AddonEntity
	 */
	public function setPackagist($packagist)
	{
		Validators::assert($packagist, 'string', 'packagist');
		UrlsHelper::assertPackagistPackageUrl($packagist);
		$this->packagist = UrlsHelper::normalizePackagistPackageUrl($packagist);
		return $this;
	}

	/**
	 * @return int
	 */
	public function getStars()
	{
		return $this->stars;
	}

	/**
	 * @param int
	 * @return AddonEntity
	 */
	public function setStars($stars)
	{
		Validators::assert($stars, 'integer', 'stars');
		$this->stars = $stars;
		return $this;
	}
}
