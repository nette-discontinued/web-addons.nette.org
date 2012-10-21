<?php

namespace NetteAddons\Model\Utils;

use Nette,
	Nette\Forms,
	Nette\Utils\Strings,
	NetteAddons\Model\Addons;


/**
 * @author Patrik Votoček
 */
class Validators extends Nette\Object
{
	/** composerName regular expression */
	const COMPOSER_NAME_RE = '^[a-z0-9]+(-[a-z0-9]+)*/[a-z0-9]+(-[a-z0-9]+)*$';

	/** @var Addons */
	private $addonsRepo;

	/** @var Licenses */
	private $licenseValidator;

	/** @var VersionParser */
	private $versionParser;



	public function __construct(Addons $addonsRepo, Licenses $licenseValidator, VersionParser $versionParser)
	{
		$this->addonsRepo = $addonsRepo;
		$this->licenseValidator = $licenseValidator;
		$this->versionParser = $versionParser;
	}



	/**
	 * @param string
	 * @return bool
	 */
	public function isComposerFullNameValid($composerFullName)
	{
		return Strings::match($composerFullName, '#' . self::COMPOSER_NAME_RE . '#');
	}



	/**
	 * @param string
	 * @return bool
	 */
	public function isComposerFullNameUnique($composerFullName)
	{
		$addon = $this->addonsRepo->findOneByComposerFullName($composerFullName);
		return ($addon === FALSE);
	}



	public function isVersionValid($versionString)
	{
		return (bool) $this->versionParser->parseTag($versionString);
	}



	public function isLicenseValid($license)
	{
		return $this->licenseValidator->isValid($license);
	}
}
