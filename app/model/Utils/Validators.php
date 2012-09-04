<?php

namespace NetteAddons\Model\Utils;

use NetteAddons\Model\Addons;
use NetteAddons\Model\Version;
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

	/** @var SpdxLicenseIdentifier */
	private $licenseValidator;



	public function __construct(Addons $addonsRepo, SpdxLicenseIdentifier $licenseValidator)
	{
		$this->addonsRepo = $addonsRepo;
		$this->licenseValidator = $licenseValidator;
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
		if (Strings::match($versionString, '#^dev-[a-z0-9._-]#i')) { // e.g. 'dev-master'
			return TRUE;
		}

		$version = new Version($versionString);
		return $version->isValid();
	}



	public function isLicenseValid($license)
	{
		return $this->licenseValidator->validate($license);
	}
}
