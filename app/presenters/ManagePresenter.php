<?php

namespace NetteAddons;

use NetteAddons\Model\Addon,
	NetteAddons\Model\Addons;
use Nette\Http\Session,
	Nette\Http\SessionSection;


final class ManagePresenter extends BasePresenter
{
	/** @var SessionSection */
	private $session;

	/** @var Addons */
	private $addons;

	/** @var string @persistent */
	private $token;

	/** @var Addon from the session. */
	private $addon;


	public function __construct(Addons $addons, Session $session)
	{
		$this->addons = $addons;
		$this->session = $session->getSection('NetteAddons.ManagePresenter');

		$this->restoreAddon();
	}

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
		return "addon->$this->token";
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



	public function actionAdd()
	{

	}



	/**
	 * Creates a new form for basic addon info.
	 * @return AddAddonForm
	 */
	protected function createComponentAddAddonForm()
	{
		$form = new AddAddonForm();
		$form->onSubmit[] = callback($this, 'addAddonFormSubmitted');

		if ($this->addon !== NULL) {
			$form->setDefaults(array(
				'name' => $this->addon->name
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

		$addon = new Addon();
		$addon->name = $values->name;

		$this->storeAddon();

		$this->flashMessage('Addon created.');

		if ($values->repository) {
			$this->redirect('versionImport');
		} else {
			$this->generateToken();
			$this->redirect('versionCreate');
		}
	}
}
