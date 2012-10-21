<?php

namespace NetteAddons\Manage\Forms;

use Nette\Security\IIdentity;


/**
 * Form for addon version creation.
 *
 * @author Patrik Votoček
 *
 * @property-write \Nette\Security\IIdentity $user
 * @property string $token
 */
class AddVersionForm extends VersionForm
{
	/** @var \Nette\Security\IIdentity */
	private $user;

	/** @var string */
	private $token;



	/**
	 * @param \Nette\Security\IIdentity
	 * @return AddVersionForm
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
		return $this->token;
	}



	/**
	 * @param string
	 * @return AddVersionForm
	 */
	public function setToken($token)
	{
		$this->token = $token;
		return $this;
	}



	protected function buildForm()
	{
		parent::buildForm();

		$this->addSubmit('sub', 'Save');

		$this->onSuccess[] = $this->process;
	}


	public function process()
	{
		$values = $this->getValues();

		try {
			$version = $this->manager->addVersionFromValues($this->addon, $values, $this->user, $this->versionParser);

		} catch (\NetteAddons\IOException $e) {
			$this['archive']->addError('Uploading file failed.');
			return;
		}

		if ($this->addon->id) {
			$this->model->add($version);

		} else {
			$this->manager->storeAddon($this->token, $this->addon);
		}
	}
}
