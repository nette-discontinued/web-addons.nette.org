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



	public function __construct(FormValidators $validators)
	{
		$this->validators = $validators;
		parent::__construct();
	}



	protected function buildForm()
	{
		$this->addText('version', 'Version', 10, 20)
			->setRequired("%label is required")
			->addRule($this->validators->isVersionValid, 'Invalid version.');

		$this->addUpload('archive', 'Archive')
			->setRequired("%label is required")
			->addRule($this->isArchiveValid, 'Only ZIP files are allowed.');

		$this->addText('license', 'License', 20, 100)
			->setRequired("%label is required")
			->addRule($this->validators->isLicenseValid, 'Invalid license identifier.')
			->setOption(
				'description',
				Html::el()->setHtml(
					'See <a href="http://www.spdx.org/licenses/">SPDX Open Source License Registry</a> for list of possible identifiers.'
				)
			);

		$this->addSubmit('create', 'Create');
	}



	public function isArchiveValid(Forms\Controls\UploadControl $control)
	{
		return Strings::endsWith($control->getValue()->getName(), '.zip');
	}
}
