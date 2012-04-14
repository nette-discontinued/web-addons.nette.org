<?php

namespace NetteAddons;



class ImportAddonForm extends BaseForm
{
	protected function buildForm()
	{
		$this->addText('repository', 'Repository URL', 60, 256)
			->addRule(self::FILLED);

		$this->addSubmit('import', 'Import');
	}
}
