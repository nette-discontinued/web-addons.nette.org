<?php

namespace NetteAddons;

use NetteAddons\Model\Addon,
	NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVersion,
	NetteAddons\Model\IAddonImporter,
	NetteAddons\Model\Facade\AddonManageFacade;
use Nette\Http\Session,
	Nette\Http\SessionSection;



final class ManagePresenter extends BasePresenter
{
	/** @var SessionSection */
	private $session;

	/** @var AddonManageFacade */
	private $manager;

	/** @var Addons */
	private $addons;

	/**
	 * @var string
	 * @persistent
	 */
	public $token;

	/** @var Addon from the session. */
	private $addon;

	/** @var \Nette\Database\Table\ActiveRow low-level database row. */
	private $addonRow;



	/**
	 * @param \NetteAddons\Model\Addons $addons
	 * @param \Nette\Http\Session $session
	 */
	public function setContext(Addons $addons, Session $session)
	{
		$this->addons = $addons;
		$this->session = $session->getSection('NetteAddons.ManagePresenter');
		$this->manager = $this->createAddonManageFacade($addons);
	}



	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect('Sign:in', $this->getApplication()->storeRequest());
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
		if ($this->token !== NULL) {
			$key = $this->getSessionKey();
			if (isset($this->session[$key])) {
				$this->addon = $this->session[$key];
				$this->addon->userId = $this->getUser()->getId();
			}
		}
	}


	/**
	 */
	protected function removeStoredAddon()
	{
		$this->addon = NULL;
		$key = $this->getSessionKey();
		unset($this->session[$key]);
	}


	/*************** Addon creation ****************/


	/**
	 * Creates a new form for basic addon info.
	 *
	 * @return AddAddonForm
	 */
	protected function createComponentAddAddonForm()
	{
		$form = new AddAddonForm($this->getContext()->formValidators);
		$form->onSuccess[] = callback($this, 'addAddonFormSubmitted');

		if ($this->addon !== NULL) {
			$form->setAddonDefaults($this->addon);
			if ($this->addon->defaultLicense) {
				$form->removeComponent($form['defaultLicense']);
			}

			if ($this->addon->composerName) {
				$form->removeComponent($form['composerName']);
			}
		}

		return $form;
	}



	/**
	 * Handles the new addon form submission.
	 *
	 * @param AddAddonForm
	 */
	public function addAddonFormSubmitted(AddAddonForm $form)
	{
		if ($this->addon === NULL) {
			$this->addon = new Addon();
		}

		try {
			// fill addon with values
			$this->manager->fillAddonWithValues($this->addon, $form->getValues(TRUE), $this->user->identity);
			$this->storeAddon();

		} catch (\NetteAddons\DuplicateEntryException $e) {
			if ($this->addon->repository) {
				$this->flashMessage($e->getMessage());
				$this->redirect('add');

			} else {
				$form->addError($e->getMessage());
				return;
			}
		}

		if ($this->addon->repository) {
			$this->flashMessage('Addon created.');
			$this->redirect('versionImport');

		} else {
			$this->flashMessage('Addon created. Now it\'s time to add the first version.');
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
			$importer = $this->createAddonImporter($form->values->url);
		} catch (\NetteAddons\NotSupportedException $e) {
			$form['url']->addError("'{$form->values->url}' is not valid GitHub URL.");
			return;
		}

		try {
			$this->addon = $this->manager->import($importer, $this->user->identity);
			$this->storeAddon();
			$this->flashMessage('Addon has been successfully imported.');
			$this->redirect('create');

		} catch (\NetteAddons\HttpException $e) {
			if ($e->getCode() === 404) {
				$form['url']->addError("Repository with URL '{$form->values->url}' does not exist.");
			} else {
				$form['url']->addError("Importing failed because GitHub returned HTTP error #" . $e->getCode() . ".");
			}

		} catch (\NetteAddons\IOException $e) {
			$form['url']->addError("Importing failed. Try again later.");
		}
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

		if ($this->addon) {
			$form['license']->setDefaultValue($this->addon->defaultLicense);
		}

		return $form;
	}


	/**
	 * @param \NetteAddons\AddVersionForm $form
	 */
	public function addVersionFormSubmitted(AddVersionForm $form)
	{
		$values = $form->getValues();

		try {
			$this->manager->addVersionFromValues($this->addon, $values, $this->getUser()->getIdentity());
			$this->storeAddon();

		} catch (\NetteAddons\IOException $e) {
			$form['archive']->addError('Uploading file failed.');
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
			$importer = $this->createAddonImporter($this->addon->repository);
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
		$importer = $this->createAddonImporter($this->addon->repository);
		$this->manager->importVersions($this->addon, $importer, $this->getUser()->getIdentity());

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
			$this->addons->add($this->addon);
			$this->removeStoredAddon();
			$this->flashMessage('Addon was successfully registered.');

		} catch (\NetteAddons\DuplicateEntryException $e) {
			$this->flashMessage("Adding new addon failed.", 'danger');
		}


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

	public function renderEdit()
	{
		$this->template->addon = $this->addon;
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



	/**
	 * Addon importer factory
	 *
	 * @param  string
	 * @return Model\IAddonImporter
	 * @throws \NetteAddons\NotSupportedException
	 */
	private function createAddonImporter($url)
	{
		return $this->getContext()->repositoryImporterFactory->createFromUrl($url);
	}



	private function createAddonManageFacade($addons)
	{
		$currentUrl = $this->getHttpRequest()->getUrl();
		return new AddonManageFacade(
			$addons,
			$this->context->parameters['uploadDir'],
			$currentUrl->getHostUrl() . rtrim($currentUrl->getBasePath(), '/') . $this->context->parameters['uploadUri']
		);
	}
}
