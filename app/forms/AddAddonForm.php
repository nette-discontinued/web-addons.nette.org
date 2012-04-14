<?php

namespace NetteAddons;



class AddAddonForm extends BaseForm
{
	public function buildForm()
	{
		$this->addText('name', 'Název', 40, 100)
			->addRule(self::FILLED);
		$this->addTextArea('description', 'Popis', 60, 10)
			->addRule(self::FILLED);
	}

}
