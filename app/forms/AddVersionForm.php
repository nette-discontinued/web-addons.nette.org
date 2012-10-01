<?php

namespace NetteAddons;

use NetteAddons\Model\Utils\FormValidators;
use Nette\Forms;
use Nette\Utils\Html;
use Nette\Utils\Strings;



/**
 * Form for addon version creation.
 */
class AddVersionForm extends BaseForm
{
	/** @var FormValidators */
	private $validators;

	/** @var Model\Utils\Licenses */
	private $licenses;



	public function __construct(FormValidators $validators, Model\Utils\Licenses $licenses)
	{
		$this->validators = $validators;
		$this->licenses = $licenses;
		parent::__construct();
	}



	protected function buildForm()
	{
		$this->addText('version', 'Version', 20)
			->setRequired("%label is required")
			->addRule($this->validators->isVersionValid, 'Invalid version.');

		$this->addMultiSelect('license', 'License', $this->licenses->getLicenses(TRUE))
			->setAttribute('class', 'chzn-select')
			->setAttribute('style', 'width: 500px;')
			->setRequired()
			->addRule($this->validators->isLicenseValid, 'Invalid license identifier.');

		$this->addSelect('how', 'How would you like to provide source codes?', array(
			'link' => 'Provide link to distribution archive.',
			'upload' => 'Upload archive to this site.',
		))->setRequired()
			->addCondition(self::EQUAL, 'link')->toggle('xlink')
			->addCondition(self::EQUAL, 'upload')->toggle('xupload');

		$this->addText('archiveLink', 'Link to ZIP archive')
			->setOption('id', 'xlink')
			->addConditionOn($this['how'], self::EQUAL, 'link')
				->addRule(self::FILLED, "%label is required")
				->addRule(self::URL, 'Please provide valid URL.');

		$this->addUpload('archive', 'Archive')
			->setOption('id', 'xupload')
			->addConditionOn($this['how'], self::EQUAL, 'upload')
				->addRule(self::FILLED, "%label is required")
				->addRule($this->isArchiveValid, 'Only ZIP files are allowed.');

		$this->addSubmit('create', 'Create');
	}



	public function isArchiveValid(Forms\Controls\UploadControl $control)
	{
		return Strings::endsWith($control->getValue()->getName(), '.zip');
	}
}
