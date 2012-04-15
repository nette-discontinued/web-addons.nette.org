<?php

namespace NetteAddons;



/**
 *
 */
class ImportAddonForm extends BaseForm
{
	protected function buildForm()
	{
		$this->addText('url', 'Repository URL', 60, 256)
			->setRequired()
			->addRule(self::URL, 'Invalid GitHub URL.');

		$this->addSubmit('import', 'Import');
	}
}
