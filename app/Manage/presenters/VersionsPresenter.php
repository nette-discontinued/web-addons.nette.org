<?php

namespace NetteAddons\Manage;

use NetteAddons\Model\Users,
	NetteAddons\Model\AddonVersions;


/**
 * @author Patrik Votoček
 */
final class VersionsPresenter extends BasePresenter
{
	/** @var Forms\AddVersionForm */
	private $addVersionForm;

	/** @var \NetteAddons\Model\AddonVersions */
	private $versions;

	/** @var \NetteAddons\Model\Users */
	private $users;



	/**
	 * @param Forms\AddVersionForm
	 */
	public function injectAddForm(Forms\AddVersionForm $addVersionForm)
	{
		$this->addVersionForm = $addVersionForm;
	}



	/**
	 * @param \NetteAddons\Model\AddonVersions
	 * @param \NetteAddons\Model\Users
	 */
	public function injectTables(AddonVersions $versions, Users $users)
	{
		$this->versions = $versions;
		$this->users = $users;
	}



	/**
	 * @return Forms\AddVersionForm
	 */
	protected function createComponentAddVersionForm()
	{
		$form = $this->addVersionForm;

		$form->setAddon($this->addon);
		$form->setToken($this->token);
		$form->setUser($this->getUser()->identity);

		$form->onSuccess[] = $this->addVersionFormSubmitted;

		return $form;
	}



	/**
	 * @param Forms\AddVersionForm
	 */
	public function addVersionFormSubmitted(Forms\AddVersionForm $form)
	{
		if ($form->valid) {
			$this->token = $form->token;

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
			$this->error(); // @todo message
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

		$owner = $this->users->createIdentity($this->users->find($this->addon->userId));

		try {
			$importer = $this->importerManager->createFromUrl($this->addon->repository);
			$result = $this->manager->updateVersions($this->addon, $importer, $owner);

		} catch (\NetteAddons\IOException $e) {
			$this->flashMessage('Version importing failed. Try again later.', 'error');
			$this->redirect(':Detail:', $this->addon->id);
		}

		if (count($result['conflicted']) === 0 && count($result['new']) === 0) {
			$this->flashMessage('Nothing new...');
		} else {
			try {
				foreach ($result['new'] as $version) {
					$this->versions->add($version);
				}
				foreach ($result['conflicted'] as $conflict) {
					$conflict['b']->id = $conflict['a']->id;
					$this->versions->update($conflict['b']);
				}
				$this->flashMessage('Versions has been updated.');
			} catch (\PDOException $e) {
				$this->flashMessage('Version importing failed. Try again later.', 'error');
			}
		}

		$this->redirect(':Detail:', $this->addon->id);
	}

}
