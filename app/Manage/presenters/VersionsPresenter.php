<?php

namespace NetteAddons\Manage;

use NetteAddons\Model\Users,
	NetteAddons\Model\AddonVersions;


/**
 * @author Patrik Votoček
 */
final class VersionsPresenter extends BasePresenter
{
	/**
	 * @var Forms\AddVersionFormFactory
	 * @inject
	 */
	public $addVersionForm;

	/**
	 * @var \NetteAddons\Model\AddonVersions
	 * @inject
	 */
	public $versions;

	/**
	 * @var \NetteAddons\Model\Users
	 * @inject
	 */
	public $users;



	/**
	 * @return Forms\VersionForm
	 */
	protected function createComponentAddVersionForm()
	{
		$form = $this->addVersionForm->create($this->addon, $this->getUser()->getIdentity(), $this->token);

		$form->onSuccess[] = $this->addVersionFormSubmitted;

		return $form;
	}



	/**
	 * @param Forms\VersionForm
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

		} catch (\NetteAddons\NotSupportedException $e) {
			$this->error();

		} catch (\NetteAddons\IOException $e) {
			$this->flashMessage('Version importing failed. Try again later.', 'error');
			$this->redirect(':Detail:', $this->addon->id);
		}

		if (count($result['conflicted']) === 0 && count($result['new']) === 0) {
			$this->flashMessage('Nothing new…');
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
