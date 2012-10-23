<?php

namespace NetteAddons\Manage\Forms;

use Nette\Utils\Strings,
	Nette\Security\IIdentity;


/**
 * Form for new addon registration. When importing from GitHub, most of the field should be prefilled.
 * The license input won't be visible when composer.json is available.
 *
 * @author Patrik Votoček
 *
 * @property-write \Nette\Security\IIdentity $user
 * @property string $token
 */
class AddAddonForm extends AddonForm
{
	/** @var \Nette\Security\IIdentity|NULL */
	private $user;

	/** @var string */
	private $token;



	/**
	 * @param \Nette\Security\IIdentity
	 * @return ImportAddonForm
	 */
	public function setUser(IIdentity $user)
	{
		$this->user = $user;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getToken()
	{
		if (is_null($this->token)) {
			$this->token = Strings::random();
		}
		return $this->token;
	}



	/**
	 * @param string
	 * @return AddAddonForm
	 */
	public function setToken($token)
	{
		$this->token = $token;
		return $this;
	}



	protected function buildForm()
	{
		parent::buildForm();

		$this->addSubmit('sub', 'Next');

		$this->onSuccess[] = $this->process;
	}



	public function process()
	{
		$values = $this->getValues(TRUE);

		$addon = $this->getAddon();
		$this->manager->fillAddonWithValues($addon, $values, $this->user);
		$this->manager->storeAddon($this->getToken(), $addon);
	}

}
