<?php

namespace NetteAddons;

use Nette\Utils\Strings;



/**
 * Form for addon version creation.
 */
class AddVersionForm extends BaseForm
{

	protected function buildForm()
	{
		$this->addText('version', 'Version', 10, 20)
			->setRequired("%label% is required");

		$this->addUpload('archive', 'Archive')
			->setRequired("%label% is required");

		$this->addText('license', 'License', 20, 100)
			->setRequired("%label% is required");

		$this->addSubmit('create', 'Create');
		$this->onValidate[] = callback($this, 'validateArchive');
	}


	/**
	 * @param \NetteAddons\AddVersionForm $form
	 */
	public function validateArchive(AddVersionForm $form)
	{
		/** @var $file \Nette\Http\FileUpload */
		$file = $form['archive']->getValue();
		if (!Strings::endsWith($file->getName(), '.zip')) {
			$form['archive']->addError('Only ZIP files are allowed.');
			$form->valid = FALSE;
		}
	}

}
