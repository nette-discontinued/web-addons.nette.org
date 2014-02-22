<?php

namespace NetteAddons\Manage;

use NetteAddons\Forms\Form;


final class AddonPresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var Forms\AddAddonFormFactory
	 */
	public $addAddonForm;

	/**
	 * @inject
	 * @var Forms\EditAddonFormFactory
	 */
	public $editAddonForm;

	/**
	 * @inject
	 * @var Forms\ImportAddonFormFactory
	 */
	public $importAddonForm;

	/**
	 * @inject
	 * @var \NetteAddons\Model\Utils\Validators
	 */
	public $validators;


	/**
	 * @return Forms\AddonForm
	 */
	protected function createComponentAddAddonForm()
	{
		$form = $this->addAddonForm->create($this->getUser()->getIdentity(), $this->token);

		if ($this->addon) {
			$form->setAddon($this->addon);
		}

		$form->onSuccess[] = array($this, 'addAddonFormSubmitted');

		return $form;
	}


	/**
	 * @param Forms\AddonForm
	 */
	public function addAddonFormSubmitted(Forms\AddonForm $form)
	{
		if ($form->valid) {
			$values = $form->getValues();
			$this->addon = $form->getAddon();
			$this->token = $values->token;

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
	 * @return \NetteAddons\Forms\Form
	 */
	protected function createComponentImportAddonForm()
	{
		$form = $this->importAddonForm->create($this->getUser()->getIdentity());

		$form->onSuccess[] = array($this, 'importAddonFormSubmitted');

		return $form;
	}


	/**
	 * @param \NetteAddons\Forms\Form
	 */
	public function importAddonFormSubmitted(Form $form)
	{
		if ($form->valid) {
			$values = $form->getValues();
			$this->token = $values->token;

			$this->flashMessage('Addon has been successfully loaded.');
			$this->redirect('this');
		}
	}


	public function renderAdd()
	{
		$this->template->full = empty($this->token);
	}


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
			$this->flashMessage('Adding new addon failed.', 'danger');
			$this->redirect(':Manage:Addon:add');
		}
	}


	/**
	 * @return Forms\AddonForm
	 */
	protected function createComponentEditAddonForm()
	{
		if (!$this->addon) {
			$this->error('Addon not found.');
		}

		$form = $this->editAddonForm->create($this->addon);

		$form->onSuccess[] = array($this, 'editAddonFormSubmitted');

		return $form;
	}


	/**
	 * @param Forms\AddonForm
	 */
	public function editAddonFormSubmitted(Forms\AddonForm $form)
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


	/**
	 * @secured
	 * @param int
	 * @param bool
	 */
	public function handleDelete($addonId, $real = FALSE)
	{
		if (!$this->auth->isAllowed($this->addon, 'delete')) {
			$this->error('You are not allowed to delete this addon.', 403);
		}
		if ($real) {
			$this->addons->delete($this->addon);
			$this->flashMessage("Addon '{$this->addon->name}' deleted.");
			$this->redirect(':List:');
		}

		$this->addons->markAsDeleted($this->addon, $this->getUser()->identity);
		$this->flashMessage("Addon '{$this->addon->name}' marked as deleted.");
		$this->redirect(':Detail:', array($this->addon->id));
	}


	/**
	 * @secured
	 * @param int
	 */
	public function handleRestore($addonId)
	{
		if (!$this->auth->isAllowed($this->addon, 'delete')) {
			$this->error('You are not allowed to restore this addon.', 403);
		}
		if (!$this->validators->isComposerFullNameUnique($this->addon->composerFullName)) {
			$this->error('This addon has newest registered version.', 409);
		}

		$this->addons->unmarkAsDeleted($this->addon);
		$this->flashMessage("Addon '{$this->addon->name}' restored");
		$this->redirect(':Detail:', array($this->addon->id));
	}


	/**
	 * @param int
	 */
	public function actionDelete($addonId)
	{
		if (!$this->auth->isAllowed($this->addon, 'delete')) {
			$this->error('You are not allowed to delete this addon.', 403);
		}
		$this['subMenu']->setAddon($this->addon);
	}


	/**
	 * @param int
	 */
	public function renderDelete($addonId)
	{
		$this->template->addon = $this->addon;
		$this->template->newest = !$this->validators->isComposerFullNameUnique($this->addon->composerFullName);
	}
}
