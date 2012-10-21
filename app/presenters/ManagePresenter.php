<?php

namespace NetteAddons;

use NetteAddons\Forms\Form,
	NetteAddons\Model\Addon,
	NetteAddons\Model\Users,
	NetteAddons\Model\AddonVersion,
	NetteAddons\Model\AddonVersions,
	NetteAddons\Model\Utils\Validators,
	NetteAddons\Model\Utils\FormValidators,
	NetteAddons\Model\Utils\VersionParser;



final class ManagePresenter extends Manage\BasePresenter
{
	/** @var AddonVersions */
	private $versions;

	/** @var Users */
	private $users;

	/** @var Validators */
	private $validators;

	/** @var FormValidators */
	private $formValidators;

	/** @var VersionParser */
	private $versionParser;



	public function injectValidators(Validators $validators, FormValidators $formValidators)
	{
		$this->validators = $validators;
		$this->formValidators = $formValidators;
	}



	public function injectVersionParser(VersionParser $parser)
	{
		$this->versionParser = $parser;
	}



	public function injectAddons(AddonVersions $versions, Users $users)
	{
		$this->versions = $versions;
		$this->users = $users;
	}



	/**
	 * @param int|NULL addon id
	 */
	public function renderCreateVersion($addonId = NULL)
	{

	}



	/**
	 * @return Manage\Forms\AddVersionForm
	 */
	protected function createComponentAddVersionForm()
	{
		$form = $this->getContext()->addAddonVersionForm;

		$form->setAddon($this->addon);
		$form->setToken($this->token);
		$form->setUser($this->getUser()->identity);

		$form->onSuccess[] = $this->addVersionFormSubmitted;

		return $form;
	}



	/**
	 * @param Manage\Forms\AddVersionForm
	 */
	public function addVersionFormSubmitted(Manage\Forms\AddVersionForm $form)
	{
		if ($form->valid) {
			$this->token = $form->token;

			if ($this->addon->id) {
				$this->flashMessage('Version created.');
				$this->redirect('Detail:', $this->addon->id);

			} else {
				$this->redirect('finish');
			}
		}
	}



	/**
	 * @param int
	 */
	public function renderCheckVersions($addonId)
	{
		if (!$this->addon->repositoryHosting) {
			$this->error();
		}

		$owner = $this->users->createIdentity($this->users->find($this->addon->userId));

		try {
			$importer = $this->createAddonImporter($this->addon->repository);
			$result = $this->manager->updateVersions($this->addon, $importer, $owner);

		} catch (\NetteAddons\IOException $e) {
			$this->flashMessage('Version importing failed. Try again later.', 'error');
			$this->redirect('Detail:', $this->addon->id);
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

		$this->redirect('Detail:', $this->addon->id);
	}



	public function handleImportVersions()
	{
		if (!$this->addon) {
			$this->error();
		}

		try {
			$importer = $this->createAddonImporter($this->addon->repository);
			$this->manager->importVersions($this->addon, $importer, $this->getUser()->getIdentity());
			$this->manager->storeAddon($this->getSessionKey(), $this->addon);
			$this->redirect('finish');

		} catch (\NetteAddons\NotSupportedException $e) {
			$this->error();
		}
	}



	/**
	 * Finish the addon creation
	 */
	public function actionFinish()
	{
		if ($this->addon === NULL) {
			$this->error();
		}

		try {
			$this->addons->add($this->addon);
			$this->manager->destroyAddon($this->getSessionKey());
			$this->flashMessage('Addon was successfully registered.');
			$this->redirect('Detail:', $this->addon->id);

		} catch (\NetteAddons\DuplicateEntryException $e) {
			$this->flashMessage("Adding new addon failed.", 'danger');
			$this->redirect(':Manage:Create:add');
		}
	}
}
