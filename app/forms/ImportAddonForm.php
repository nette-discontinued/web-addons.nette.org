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
			->setAttribute('autofocus', TRUE)
			->setRequired();

		$this->addSubmit('import', 'Import');
	}
}
