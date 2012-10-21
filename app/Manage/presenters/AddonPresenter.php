<?php

namespace NetteAddons\Manage;


/**
 * @author Patrik Votoček
 */
final class AddonPresenter extends BasePresenter
{
	/** @var Forms\AddAddonForm */
	private $addAddonForm;

	/** @var Forms\EditAddonForm */
	private $editAddonForm;

	/** @var Forms\ImportAddonForm */
	private $importAddonForm;



	/**
	 * @param Forms\AddAddonForm
	 */
	public function injectAddForm(Forms\AddAddonForm $addAddonForm)
	{
		$this->addAddonForm = $addAddonForm;
	}



	/**
	 * @param Forms\EditAddonForm
	 */
	public function injectEditForm(Forms\EditAddonForm $editAddonForm)
	{
		$this->editAddonForm = $editAddonForm;
	}



	/**
	 * @param Forms\ImportAddonForm
	 */
	public function injectImportForm(Forms\ImportAddonForm $importAddonForm)
	{
		$this->importAddonForm = $importAddonForm;
	}



	/**
	 * Creates a new form for addon information.
	 *
	 * @return Forms\AddAddonForm
	 */
	protected function createComponentAddAddonForm($name)
	{
		$form = $this->addAddonForm;

		if ($this->addon) {
			$form->setAddon($this->addon);
		}
		$form->setUser($this->getUser()->identity);
		$form->setToken($this->token);

		$form->onSuccess[] = $this->addAddonFormSubmitted;

		return $form;
	}



	/**
	 * Handles the new addon form submission.
	 *
	 * @param Forms\AddAddonForm
	 */
	public function addAddonFormSubmitted(Forms\AddAddonForm $form)
	{
		if ($form->valid) {
			$this->addon = $form->addon;
			$this->token = $form->token;

			$imported = (bool) $this->addon->repositoryHosting; // TODO: remove

			if ($imported) {
				$this->redirect(':Manage:Versions:import');

			} else {
				$this->flashMessage('Addon created. Now it\'s time to add the first version.');
				$this->redirect(':Manage:Versions:add');
			}
		}
	}



	/**
	 * @return Forms\ImportAddonForm
	 */
	protected function createComponentImportAddonForm()
	{
		$form = $this->importAddonForm;

		$form->setUser($this->getUser()->identity);

		$form->onSuccess[] = $this->importAddonFormSubmitted;

		return $form;
	}



	/**
	 * @param Forms\ImportAddonForm
	 */
	public function importAddonFormSubmitted(Forms\ImportAddonForm $form)
	{
		if ($form->valid) {
			$this->token = $form->token;

			$this->flashMessage('Addon has been successfully loaded.');
			$this->redirect('this');
		}
	}



	public function renderAdd()
	{
		$this->template->full = empty($this->token);
	}



	/**
	 * Finish the addon creation
	 */
	public function actionFinish()
	{
		if (!$this->addon) {
			$this->error('Addon not found.');
		}
		if (!$this->token) {
			$this->error('Invalid token.');
		}

		try {
			$this->addons->add($this->addon);
			$this->manager->destroyAddon($this->token);
			$this->flashMessage('Addon was successfully registered.');
			$this->redirect(':Detail:', $this->addon->id);

		} catch (\NetteAddons\DuplicateEntryException $e) {
			$this->flashMessage("Adding new addon failed.", 'danger');
			$this->redirect(':Manage:Addon:add');
		}
	}



	/**
	 * @return Forms\EditAddonForm
	 */
	protected function createComponentEditAddonForm()
	{
		if (!$this->addon) {
			$this->error('Addon not found.');
		}

		$form = $this->editAddonForm;

		$form->setAddon($this->addon);

		$form->onSuccess[] = $this->editAddonFormSubmitted;

		return $form;
	}



	/**
	 * @param Forms\EditAddonForm
	 */
	public function editAddonFormSubmitted(Forms\EditAddonForm $form)
	{
		if ($form->valid) {
			$this->addon = $form->addon;

			$this->flashMessage('Addon saved.');
			$this->redirect(':Detail:', $this->addon->id);
		}
	}



	/**
	 * @param int
	 */
	public function actionEdit($addonId)
	{
		if (!$this->addon) {
			$this->error('Addon not found.');
		}
		$this['subMenu']->setAddon($this->addon);
	}



	/**
	 * @param int
	 */
	public function renderEdit($addonId)
	{
		$this->template->addon = $this->addon;
	}

}
