<?php

namespace NetteAddons\Model\Utils;

use NetteAddons\Model\Addons;
use Nette;
use Nette\Forms;
use Nette\Utils\Strings;
use Composer\Util\SpdxLicenseIdentifier;



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



	public function isComposerNameValid($composerName)
	{
		return Strings::match($composerName, '#' . self::COMPOSER_NAME_RE . '#');
	}



	public function isComposerNameUnique($composerName)
	{
		$addon = $this->addonsRepo->findOneBy(array(
			'composerName' => $composerName,
		));
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
