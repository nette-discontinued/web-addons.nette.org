<?php

namespace NetteAddons\Forms;



class EditAddonForm extends AddAddonForm
{
	protected function buildForm()
	{
		parent::buildForm();

		$this->removeComponent($this['composerName']);
		$this['create']->caption = 'Save';
	}
}
