<?php

namespace NetteAddons\Manage\Forms;

use Nette\Utils\Strings;
use Nette\Forms\Controls\UploadControl;
use NetteAddons\Model\Addon;
use NetteAddons\Model\Utils\Licenses;
use NetteAddons\Model\Utils\FormValidators;


class VersionForm extends \NetteAddons\Forms\BaseForm
{
	/** @var \NetteAddons\Model\Utils\FormValidators */
	private $validators;

	/** @var \NetteAddons\Model\Utils\Licenses */
	private $licenses;

	/** @var \NetteAddons\Model\Addon */
	private $addon;


	public function __construct(FormValidators $validators, Licenses $licenses, Addon $addon)
	{
		$this->validators = $validators;
		$this->licenses = $licenses;
		$this->addon = $addon;

		parent::__construct();
	}


	protected function buildForm()
	{
		$this->addText('version', 'Version', NULL, 100)
			->setRequired()
			->addRule(array($this->validators, 'isVersionValid'), 'Invalid version.');

		$this->addMultiSelect('license', 'License', $this->licenses->getLicenses(TRUE))
			->setAttribute('class', 'chzn-select')
			->setRequired()
			->addRule(array($this->validators, 'isLicenseValid'), 'Invalid license identifier.');

		$this->addHidden('how', 'link');

		$this->addText('archiveLink', 'Link to ZIP archive')
			->setRequired()
			->addRule(self::URL);

		$license = $this->addon->defaultLicense;
		if (is_string($license)) {
			if ($license === 'NOLICENSE') {
				$license = array();
			} else {
				$license = array_map('trim', explode(',', $license));
			}
		}

		$this->setDefaults(array(
			'license' => $license,
		));
	}
}
