<?php

namespace NetteAddons\Manage\Forms;

use Nette\Utils\Strings,
	Nette\Security\IIdentity;


/**
 * Form for new addon registration. When importing from GitHub, most of the fields should be prefilled.
 * The license input won't be visible when composer.json is available.
 *
 * @author Patrik Votoček
 */
class AddAddonFormFactory extends AddonFormFactory
{

	/**
	 * @param IIdentity
	 * @param string
	 * @return AddonForm
	 */
	public function create(IIdentity $user, $token)
	{
		$form = $this->createForm();

		$form->addHidden('token', is_null($token) ? Strings::random() : $token);
		$form->addSubmit('sub', 'Next');

		$manager = $this->manager;
		$form->onSuccess[] = function(AddonForm $form) use($manager, $user) {
			$addon = $form->getAddon();
			$values = $form->getValues(TRUE);

			$manager->fillAddonWithValues($addon, $values, $user);
			$manager->storeAddon($values['token'], $addon);
		};

		return $form;
	}

}
