<?php

namespace NetteAddons;



class EditAddonForm extends AddAddonForm
{
	protected function buildForm()
	{
		parent::buildForm();

		$this['create']->caption = 'Save';
	}
}
