<?php

namespace NetteAddons;

use NetteAddons\Model\Addon;



/**
 * Form for new addon registration. When importing from GitHub, most of the field should be prefilled.
 * The license input won't be visible when composer.json is available.
 */
class AddAddonForm extends BaseForm
{
	protected function buildForm()
	{
		$this->addText('name', 'Name', 40, 100)
			->setRequired();
		$this->addText('shortDescription', 'Short description', 60, 4)
			->setAttribute('class', 'span4')
			->setRequired();
		$this->addTextArea('description', 'Description', 80, 20)
			->setAttribute('class', 'span6');
		$this->addText('license', 'License')
			->setRequired();
		$this->addText('demo', 'Demo URL:', 60, 500)
			->setAttribute('class', 'span6');
		// $this->addText('tags');
		$this->addSubmit('create', 'Next');
	}



	/**
	 * Sets default values. Used when importing from GitHub.
	 *
	 * @param Addon
	 */
	public function setAddonDefaults(Addon $addon)
	{
		$this->setDefaults(array(
			'name' => $addon->name,
			'shortDescription' => $addon->shortDescription,
			'description' => $addon->description,
			'demo' => $addon->demo
		));
	}
}
