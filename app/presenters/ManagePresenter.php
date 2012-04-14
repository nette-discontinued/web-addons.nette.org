<?php

namespace NetteAddons;

use NetteAddons\Model\Addon,
	NetteAddons\Model\AddonVersion,
	NetteAddons\Model\Addons;
use Nette\Http\Session,
	Nette\Http\SessionSection;



final class ManagePresenter extends BasePresenter
{
	/** @var SessionSection */
	private $session;

	/** @var Addons */
	private $addons;

	/**
	 * @var string
	 * @persistent
	 */
	public $token;

	/** @var Addon from the session. */
	private $addon;



	public function setContext(Addons $addons, Session $session)
	{
		$this->addons = $addons;
		$this->session = $session->getSection('NetteAddons.ManagePresenter');
	}



	public function startup()
	{
		parent::startup();

		$this->restoreAddon();
		bd($this->addon);
	}



	/*************** Session storage ****************/


	/**
	 * Generates a new token for the wizzard.
	 */
	private function generateToken()
	{
		$this->token = base_convert(md5(lcg_value()), 16, 36);
	}



	/**
	 * Gets the session key for the addon stored under the current token.
	 *
	 * If there is no token, triggers generation of a new one.
	 * @return string
	 */
	private function getSessionKey()
	{
		if ($this->token === NULL) {
			$this->generateToken();
		}
		return "addon-$this->token";
	}



	/**
	 * Stores the addon object into the session.
	 */
	protected function storeAddon()
	{
		$this->session[$this->getSessionKey()] = $this->addon;
	}


	/**
	 * Restores the addon object from session.
	 */
	protected function restoreAddon()
	{
		if ($this->token !== NULL && isset($this->session[$this->getSessionKey()])) {
			$this->addon = $this->session[$this->getSessionKey()];
		}
	}




	/*************** Addon creation ****************/


	/**
	 * Creates a new form for basic addon info.
	 * @return AddAddonForm
	 */
	protected function createComponentAddAddonForm()
	{
		$form = new AddAddonForm();
		$form->onSuccess[] = callback($this, 'addAddonFormSubmitted');

		if ($this->addon !== NULL) {
			$form->setDefaults(array(
				'name' => $this->addon->name,
				'shortDescription' => $this->addon->shortDescription,
				'description' => $this->addon->description
			));
		}

		return $form;
	}



	/**
	 * Handles the new addon form submission.
	 * @param \NetteAddons\AddAddonForm $form
	 */
	public function addAddonFormSubmitted(AddAddonForm $form)
	{
		$values = $form->getValues();

		if ($this->addon === NULL) {
			$this->addon = new Addon();
		}
		$this->addon->name = $values->name;
		$this->addon->shortDescription = $values->shortDescription;
		$this->addon->description = $values->description;

		$this->storeAddon();

		$this->flashMessage('Addon created.');

		if ($values->repository) {
			$this->redirect('versionImport');
		} else {
			$this->redirect('versionCreate');
		}
	}




	/*************** Addon import ****************/

	protected function createComponentImportAddonForm()
	{
		$form = new ImportAddonForm();

		$form->onSuccess[] = callback($this, 'importAddonFormSubmitted');
		return $form;
	}


	public function importAddonFormSubmitted()
	{

	}


	/*************** Create a new version ****************/

	protected function createComponentAddVersionForm()
	{
		$form = new AddVersionForm();

		$form->onSuccess[] = callback($this, 'addVersionFormSubmitted');
		return $form;
	}


	public function addVersionFormSubmitted(AddVersionForm $form)
	{
		$values = $form->getValues();

		$version = new AddonVersion();
		$version->version = $values->version;
		$this->addon->versions[] = $version;
		$this->storeAddon();

		$this->flashMessage('Version created.');
		$this->redirect('finish');
	}



	/*************** Finish the addon creation ****************/

	public function actionFinish()
	{
		if ($this->addon !== NULL) {
			$id = $this->addons->createAddon($this->addon);
			$this->flashMessage('Addon sucessfuly saved.');
			$this->redirect('Detail:', $id);
		} else {
			$this->redirect('create');
		}
	}

}
