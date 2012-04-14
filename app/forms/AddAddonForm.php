<?php

namespace NetteAddons;



class AddAddonForm extends BaseForm
{
	protected function buildForm()
	{
		$this->addText('name', 'Name', 40, 100)
			->addRule(self::FILLED);
		$this->addTextArea('shortDescription', 'Short description', 60, 4)
			->addRule(self::FILLED);
		$this->addTextArea('description', 'Description', 80, 20);

		$this->addHidden('repository');

		$this->addSubmit('create', 'Create');
	}

}
