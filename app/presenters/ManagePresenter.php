<?php

namespace NetteAddons;

use NetteAddons\Model\Addon,
	NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVersion,
	NetteAddons\Model\AddonUpdater,
	NetteAddons\Model\IAddonImporter,
	NetteAddons\Model\Facade\AddonManageFacade;
use Nette\Http\Session,
	Nette\Http\SessionSection;



final class ManagePresenter extends BasePresenter
{
	/** @var SessionSection */
	private $session;

	/** @var \NetteAddons\Model\Facade\AddonManageFacade */
	private $manager;

	/** @var AddonUpdater */
	private $updater;

	/** @var Addons */
	private $addons;

	/**
	 * @var string
	 * @persistent
	 */
	public $token;

	/** @var Addon from the session. */
	private $addon;

	/** @var \Nette\Database\Table\ActiveRow Low-level database row. */
	private $addonRow;



	/**
	 * @param \NetteAddons\Model\Facade\AddonManageFacade $manager
	 * @param \NetteAddons\Model\AddonUpdater $updater
	 * @param \NetteAddons\Model\Addons $addons
	 * @param \Nette\Http\Session $session
	 */
	public function setContext(AddonManageFacade $manager, AddonUpdater $updater, Addons $addons, Session $session)
	{
		$this->manager = $manager;
		$this->updater = $updater;
		$this->addons = $addons;
		$this->session = $session->getSection('NetteAddons.ManagePresenter');
	}



	protected function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn()) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect('Sign:in', $this->application->storeRequest());
		}

		$this->restoreAddon();
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
		$this->addon->userId = $this->getUser()->getId();
		$this->session[$this->getSessionKey()] = $this->addon;
	}


	/**
	 * Restores the addon object from session.
	 */
	protected function restoreAddon()
	{
		if ($this->token !== NULL && isset($this->session[$this->getSessionKey()])) {
			$this->addon = $this->session[$this->getSessionKey()];
			$this->addon->userId = $this->getUser()->getId();
		}
	}


	/**
	 */
	protected function removeStoredAddon()
	{
		$this->addon = NULL;
		unset($this->session[$this->getSessionKey()]);
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
			$form->setAddonDefaults($this->addon);
		}

		return $form;
	}



	/**
	 * Handles the new addon form submission.
	 * @param \NetteAddons\AddAddonForm $form
	 */
	public function addAddonFormSubmitted(AddAddonForm $form)
	{
		if ($this->addon === NULL) {
			$this->addon = new Addon();
		}

		try {
			// fill addon with values
			$this->manager->buildAddonFromValues($this->addon, $form->values, $this->user->identity);
			$this->storeAddon();

		} catch (DuplicateEntryException $e) {
			if ($this->addon->repository) {
				$this->flashMessage($e->getMessage());
				$this->redirect('add');

			} else {
				$form->addError($e->getMessage());
				return;
			}
		}

		$this->flashMessage('Addon created.');
		if ($this->addon->repository) {
			$this->redirect('versionImport');

		} else {
			$this->redirect('versionCreate');
		}
	}



	/*************** Addon import ****************/



	/**
	 * @return ImportAddonForm
	 */
	protected function createComponentImportAddonForm()
	{
		$form = new ImportAddonForm();
		$form->onSuccess[] = callback($this, 'importAddonFormSubmitted');
		return $form;
	}



	/**
	 * @param \NetteAddons\ImportAddonForm $form
	 */
	public function importAddonFormSubmitted(ImportAddonForm $form)
	{
		try {
			$importer = $this->getContext()->createRepositoryImporter($form->values->url);

		} catch (InvalidArgumentException $e) {
			$form['url']->addError('Invalid GitHub URL');
			return;
		}

		try {
			$this->addon = $this->manager->importRepositoryVersions($importer, $this->user->identity);
			$this->storeAddon();

		} catch (\UnexpectedValueException $e) {
			$form->addError($e->getMessage());
			return;

		} catch (\NetteAddons\InvalidStateException $e) {
			$form->addError($e->getMessage() . ' Probably missing license?');
			return;
		}

		$this->flashMessage('Imported addon.');
		$this->redirect('create');
	}


	/*************** Create a new version ****************/


	public function actionVersionCreate($id = NULL)
	{
		if ($id !== NULL) {
			$this->addon = Addon::fromActiveRow($this->addons->findOneBy(array('id' => $id)));
			$this->addon->userId = $this->getUser()->getId();
		}
	}


	/**
	 * @return AddVersionForm
	 */
	protected function createComponentAddVersionForm()
	{
		$form = new AddVersionForm();
		$form->onSuccess[] = callback($this, 'addVersionFormSubmitted');
		return $form;
	}


	/**
	 * @param \NetteAddons\AddVersionForm $form
	 */
	public function addVersionFormSubmitted(AddVersionForm $form)
	{
		$values = $form->getValues();

		try {
			$this->manager->submitAddonVersion($this->addon, $values);
			$this->updater->update($this->addon);
			$this->storeAddon();

		} catch (\NetteAddons\InvalidArgumentException $e) {
			$form->addError($e->getMessage());
			return;

		} catch (\NetteAddons\InvalidStateException $e) {
			$form->addError($e->getMessage() . ' Probably missing license?');
			return;
		}

		$this->flashMessage('Version created.');
		if (($id = $this->getParameter('id')) !== NULL) {
			$this->redirect('Detail:', $id);

		} else {
			$this->redirect('finish');
		}
	}



	/*************** Import versions ****************/


	/**
	 * @param int
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionCheckVersions($id)
	{
		if (($this->addonRow = $this->addons->findOneBy(array('id' => $id))) === FALSE) {
			throw new \Nette\Application\BadRequestException('Invalid addon ID.');
		}

		$this->addon = Addon::fromActiveRow($this->addonRow);
		$this->addon->userId = $this->getUser()->getId();

		try {
			$importer = $this->getContext()->createRepositoryImporter($this->addon->repository);
			$this->addon = $this->manager->importRepositoryVersions($importer, $this->user->identity);
			$this->addon->versions = $importer->importVersions();
			$this->updater->update($this->addon);

			$this->flashMessage('Addon version successfully updated.');

		} catch (\NetteAddons\InvalidStateException $e) {
			$this->flashMessage($e->getMessage() . ' Maybe missing license?');
		}

		$this->redirect('Detail:default', $id);
	}


	/**
	 *
	 */
	public function handleImportVersions()
	{
		$importer = $this->getContext()->createRepositoryImporter($this->addon->repository);
		$this->addon->versions = $importer->importVersions();
		$this->storeAddon();
		$this->redirect('finish');
	}



	/**
	 * Finish the addon creation
	 */
	public function actionFinish()
	{
		if ($this->addon === NULL) {
			$this->redirect('create');
		}

		try {
			$this->addon->userId = $this->getUser()->getId();
			$row = $this->updater->update($this->addon);
			$this->flashMessage('Addon was successfully saved.');

		} catch (\NetteAddons\InvalidStateException $e) {
			$row = $this->addons->findBy(array('composerName' => $this->addon->composerName));
			$this->flashMessage("Addon cannot be imported. " . $e->getMessage(), 'danger');
		}
		$this->removeStoredAddon();

		if (isset($row->id)) {
			$this->redirect('Detail:', $row->id);

		} else {
			$this->redirect('Homepage:');
		}
	}


	/*************** Addon editing ****************/


	/**
	 * @param $id
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionEdit($id)
	{
		if (($this->addonRow = $this->addons->findOneBy(array('id' => $id))) === FALSE) {
			throw new \Nette\Application\BadRequestException('Invalid addon ID.');
		}
		$this->addon = Addon::fromActiveRow($this->addonRow);
	}


	/**
	 * @return EditAddonForm
	 */
	protected function createComponentEditAddonForm()
	{
		$form = new EditAddonForm();
		$form->setAddonDefaults($this->addon);
		$form->onSuccess[] = callback($this, 'editAddonFormSubmitted');
		return $form;
	}


	/**
	 * @param \NetteAddons\EditAddonForm $form
	 */
	public function editAddonFormSubmitted(EditAddonForm $form)
	{
		$values = $form->getValues();

		$this->addonRow->name = $values->name;
		$this->addonRow->shortDescription = $values->shortDescription;
		$this->addonRow->description = $values->description;
		$this->addonRow->demo = $values->demo;
		$this->addonRow->update();

		$this->flashMessage('Addon saved.');
		$this->redirect('Detail:', $this->addonRow->id);
	}

}
