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

		$providers = array(
			'link' => 'Provide link to distribution archive.',
			'upload' => 'Upload archive to this site.',
		);
		$this->addSelect('how', 'How would you like to provide source codes?', $providers)
			->setRequired()
			->addCondition(self::EQUAL, 'link')
				->toggle('xlink')
				->endCondition()
			->addCondition(self::EQUAL, 'upload')
				->toggle('xupload');

		$this->addText('archiveLink', 'Link to ZIP archive')
			->setOption('id', 'xlink')
			->addConditionOn($this['how'], self::EQUAL, 'link')
				->addRule(self::FILLED)
				->addRule(self::URL);

		$this->addUpload('archive', 'Archive')
			->setOption('id', 'xupload')
			->addConditionOn($this['how'], self::EQUAL, 'upload')
				->addRule(self::FILLED)
				->addRule(array($this, 'isArchiveValid'), 'Only ZIP files are allowed.');

		$license = $this->addon->defaultLicense;
		if (is_string($license)) {
			$license = array_map('trim', explode(',', $license));
		}
		$this->setDefaults(array(
			'license' => $license,
		));
	}


	/**
	 * @param \Nette\Forms\Controls\UploadControl
	 * @return bool
	 */
	public function isArchiveValid(UploadControl $control)
	{
		return Strings::endsWith($control->value->name, '.zip');
	}
}
