<?php

namespace NetteAddons;



class EditAddonForm extends AddAddonForm
{
	public function buildForm()
	{
		parent::buildForm();

		$this['create']->caption = 'Save';
	}
}
