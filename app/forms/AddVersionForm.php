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
			->setRequired("%label is required")
			->addRule(callback($this, 'validateVersion'), 'Invalid version.');

		$this->addUpload('archive', 'Archive')
			->setRequired("%label is required");

		$this->addText('license', 'License', 20, 100)
			->setRequired("%label is required");

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

	/**
	 * Checks whether version is in valid format.
	 *
	 * @author Jan Tvrdík
	 * @param  \Nette\Forms\Controls\TextInput
	 * @return bool
	 */
	public function validateVersion(\Nette\Forms\Controls\TextInput $control)
	{
		$version = new Model\Version($control->getValue());
		return $version->isValid();
	}

}
