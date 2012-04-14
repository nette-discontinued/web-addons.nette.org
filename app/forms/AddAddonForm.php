<?php

namespace NetteAddons;



class AddAddonForm extends BaseForm
{
	protected function buildForm()
	{
		$this->addText('name', 'Name', 40, 100)
			->addRule(self::FILLED);
		$this->addTextArea('shortDescription', 'Short description', 60, 4)
			->setAttribute('class', 'span4')
			->addRule(self::FILLED);
		$this->addTextArea('description', 'Description', 80, 20)
			->setAttribute('class', 'span6');

		$this->addText('demo', 'Demo URL:', 60, 500)
			->setAttribute('class', 'span6');

		$this->addSubmit('create', 'Next');
	}

}
