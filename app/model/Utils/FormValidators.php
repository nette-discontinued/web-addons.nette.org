<?php

namespace NetteAddons\Model\Utils;

use NetteAddons\Model\Addons;
use NetteAddons\Model\Version;
use Nette;
use Nette\Forms;
use Nette\Utils\Strings;
use Composer\Util\SpdxLicenseIdentifier;



class FormValidators extends Nette\Object
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



	public function isComposerNameValid(Forms\IControl $control)
	{
		return Strings::match($control->getValue(), self::COMPOSER_NAME_RE);
	}



	public function isComposerNameUnique(Forms\IControl $control)
	{
		$addon = $this->addonsRepo->findOneBy(array(
			'composerName' => $control->getValue(),
		));
		return ($addon === FALSE);
	}


	public function isVersionValid(Forms\IControl $control)
	{
		$version = new Version($control->getValue());
		return $version->isValid();
	}


	public function isLicenseValid(Forms\IControl $control)
	{
		return $this->licenseValidator->validate($control->getValue());
	}
}
