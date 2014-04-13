<?php

namespace NetteAddons\Model\Utils;

use Nette\Utils\Strings;
use NetteAddons\Model\Addon;
use NetteAddons\Model\Addons;


class Validators extends \Nette\Object
{
	/** composerName regular expression */
	const COMPOSER_NAME_RE = '^[a-z0-9]+(-[a-z0-9]+)*/[a-z0-9]+(-[a-z0-9]+)*$';

	/** @var string[]|array */
	private $protectedVendors = array('nette', 'page', 'special', 'api');

	/** @var \NetteAddons\Model\Addons */
	private $addonsRepo;

	/** @var \NetteAddons\Model\Utils\Licenses */
	private $licenseValidator;

	/** @var \NetteAddons\Model\Utils\VersionParser */
	private $versionParser;


	public function __construct(Addons $addonsRepo, Licenses $licenseValidator, VersionParser $versionParser)
	{
		$this->addonsRepo = $addonsRepo;
		$this->licenseValidator = $licenseValidator;
		$this->versionParser = $versionParser;
	}


	public function isComposerVendorNameProtectionFree($composerFullName)
	{
		$composerVendor = NULL;
		if (($data = Strings::match($composerFullName, Addon::COMPOSER_NAME_RE)) !== NULL) {
			$composerVendor = Strings::lower($data['vendor']);
		}

		return !in_array($composerVendor, $this->protectedVendors);
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
		return $addon === FALSE;
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
