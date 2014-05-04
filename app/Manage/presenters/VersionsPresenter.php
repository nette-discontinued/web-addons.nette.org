<?php

namespace NetteAddons\Manage;


final class VersionsPresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Manage\Forms\AddVersionFormFactory
	 */
	public $addVersionForm;

	/**
	 * @inject
	 * @var \NetteAddons\Model\AddonVersions
	 */
	public $versions;

	/**
	 * @inject
	 * @var \NetteAddons\Services\AddonUpdaterService
	 */
	public $addonUpdaterService;

	/**
	 * @inject
	 * @var \NetteAddons\Model\Users
	 */
	public $users;


	/**
	 * @return Forms\VersionForm
	 */
	protected function createComponentAddVersionForm()
	{
		$form = $this->addVersionForm->create($this->addon, $this->getUser()->getIdentity(), $this->token);

		$form->onSuccess[] = array($this, 'addVersionFormSubmitted');

		return $form;
	}


	/**
	 * @param \NetteAddons\Manage\Forms\VersionForm
	 */
	public function addVersionFormSubmitted(Forms\VersionForm $form)
	{
		if ($form->valid) {
			$values = $form->getValues();
			$this->token = $values->token;

			if ($this->addon->id) {
				$this->flashMessage('Version created.');
				$this->redirect(':Detail:', $this->addon->id);

			} else {
				$this->redirect(':Manage:Addon:finish');
			}
		}
	}


	public function actionAdd()
	{
		if (!$this->addon) {
			$this->error('Addon not found.');
		}
	}


	public function renderAdd()
	{
		$this->template->addon = $this->addon;
	}


	public function actionImport()
	{
		if (!$this->addon) {
			$this->error('Addon not found.');
		}
		if (!$this->token) {
			$this->error('Invalid token.');
		}

		try {
			$importer = $this->importerManager->createFromUrl($this->addon->repository);
			$this->manager->importVersions($this->addon, $importer, $this->getUser()->identity);
			$this->manager->storeAddon($this->token, $this->addon);
			$this->redirect(':Manage:Addon:finish');
		} catch (\NetteAddons\NotSupportedException $e) {
			$this->error();
		}
	}


	/**
	 * @param int
	 */
	public function actionCheck($addonId)
	{
		if (!$this->addon) {
			$this->error('Addon not found.');
		}

		try {
			$this->addonUpdaterService->updateAddon($this->addon->id);
			$this->flashMessage('Versions have been updated.');
		} catch (\Exception $e) {
			$this->flashMessage('Version importing failed. Try again later.', 'error');
			$this->redirect(':Detail:', $this->addon->id);
		}

		$this->redirect(':Detail:', $this->addon->id);
	}
}
