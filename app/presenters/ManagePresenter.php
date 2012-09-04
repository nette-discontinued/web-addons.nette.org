<?php

namespace NetteAddons;

use NetteAddons\Model\Addon;
use NetteAddons\Model\Addons;
use NetteAddons\Model\AddonVersion;
use NetteAddons\Model\IAddonImporter;
use NetteAddons\Model\Authorizator;
use NetteAddons\Model\Facade\AddonManageFacade;
use NetteAddons\Model\Utils\FormValidators;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Utils\Strings;



final class ManagePresenter extends BasePresenter
{
	/**
	 * @var string token used for storing addon in session
	 * @persistent
	 */
	public $token;

	/**
	 * @var int
	 * @persistent
	 */
	public $addonId;

	/** @var SessionSection */
	private $session;

	/** @var AddonManageFacade */
	private $manager;

	/** @var Addons */
	private $addons;

	/** @var FormValidators */
	private $formValidators;

	/** @var Authorizator */
	private $authorizator;

	/** @var Addon|NULL from the session. */
	private $addon;



	/**
	 * @param  Addons
	 * @param  Session
	 * @param  FormValidators
	 * @param  Authorizator
	 * @return void
	 */
	public function setContext(Addons $addons, Session $session, FormValidators $formValidators, Authorizator $authorizator)
	{
		$this->addons = $addons;
		$this->session = $session->getSection('NetteAddons.ManagePresenter');
		$this->formValidators = $formValidators;
		$this->authorizator = $authorizator;
		$this->manager = $this->createAddonManageFacade($addons);
	}



	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect('Sign:in', $this->getApplication()->storeRequest());
		}

		if ($this->token && $this->addonId) {
			$this->error('Parameters token and addonId must not be present at the same time.');
		}

		if ($this->token) {
			$this->restoreAddon();
		} elseif ($this->addonId) {
			$row = $this->addons->find($this->addonId);
			if (!$row) $this->error();
			$this->addon = Addon::fromActiveRow($row);
		}

		if ($this->addon && !$this->authorizator->isAllowed($this->addon, 'manage')) {
			$this->error('You are not allowed to manage this addon.', 403);
		}
	}



	/**
	 * Creates a new form for addon information.
	 *
	 * @return AddAddonForm
	 */
	protected function createComponentAddAddonForm()
	{
		$form = new AddAddonForm($this->formValidators);
		$form->onSuccess[] = $this->addAddonFormSubmitted;

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

		$this->manager->fillAddonWithValues($this->addon, $form->getValues(TRUE), $this->getUser()->getIdentity());
		$this->storeAddon();

		if ($this->addon->repository) { // TODO: use more reliable method
			$this->flashMessage('Addon created.');
			$this->redirect('importVersions');

		} else {
			$this->flashMessage('Addon created. Now it\'s time to add the first version.');
			$this->redirect('createVersion');
		}
	}



	/**
	 * @return ImportAddonForm
	 */
	protected function createComponentImportAddonForm()
	{
		$form = new ImportAddonForm();
		$form->onSuccess[] = $this->importAddonFormSubmitted;
		return $form;
	}



	/**
	 * @param ImportAddonForm
	 */
	public function importAddonFormSubmitted(ImportAddonForm $form)
	{
		try {
			$url = $form->getValues()->url;
			$importer = $this->createAddonImporter($url);

		} catch (\NetteAddons\NotSupportedException $e) {
			$form['url']->addError("'$url' is not valid GitHub URL.");
			return;
		}

		try {
			$this->addon = $this->manager->import($importer, $this->getUser()->getIdentity());

			if ($this->addon->composerName && !$this->context->validators->isComposerNameUnique($this->addon->composerName)) {
				$form->addError("Addon with composer name '{$this->addon->composerName}' already exist.");
				return;
			}

			$this->storeAddon();
			$this->flashMessage('Addon has been successfully imported.');
			$this->redirect('createAddon');

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



	/**
	 * @param int|NULL addon id
	 */
	public function actionCreateVersion($id = NULL)
	{
		if ($id !== NULL) { // we're manually adding new version to an already existing addon
			if ($this->addon !== NULL) {
				$this->error("Invalid request. Parameters token and id must not be present at the same time.");
			}
			$row = $this->addons->find($id);
			if (!$row) {
				$this->error("Addon with ID #$id does not exist.");
			}
			$this->addon = Addon::fromActiveRow($row);
			if (!$this->authorizator->isAllowed($this->addon, 'manage')) {
				$this->error("You are not allowed to manage this addon.", 403);
			}
		}
	}



	/**
	 * @return AddVersionForm
	 */
	protected function createComponentAddVersionForm()
	{
		$form = new AddVersionForm($this->formValidators);
		$form->onSuccess[] = $this->addVersionFormSubmitted;

		if ($this->addon) {
			$form->setDefaults(array(
				'license' => $this->addon->defaultLicense,
			));
		}

		return $form;
	}



	/**
	 * @param AddVersionForm
	 */
	public function addVersionFormSubmitted(AddVersionForm $form)
	{
		try {
			$values = $form->getValues();
			$this->manager->addVersionFromValues($this->addon, $values, $this->getUser()->getIdentity());
			$this->storeAddon();
			$this->flashMessage('Version created.');

		} catch (\NetteAddons\IOException $e) {
			$form['archive']->addError('Uploading file failed.');
			return;
		}

		if (($id = $this->getParameter('id')) !== NULL) { // TODO: better
			$this->redirect('Detail:', $id);

		} else {
			$this->redirect('finish');
		}
	}



	/**
	 * @param int
	 */
	public function actionCheckVersions($id)
	{
		throw new \NetteAddons\NotImplementedException();
		/*if (($this->addonRow = $this->addons->find($id)) === FALSE) {
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

		$this->redirect('Detail:default', $id);*/
	}



	public function handleImportVersions()
	{
		if (!$this->addon) {
			$this->error();
		}

		try {
			$importer = $this->createAddonImporter($this->addon->repository);
			$this->manager->importVersions($this->addon, $importer, $this->getUser()->getIdentity());
			$this->storeAddon();
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
			$this->removeStoredAddon();
			$this->flashMessage('Addon was successfully registered.');
			$this->redirect('Detail:', $this->addon->id);

		} catch (\NetteAddons\DuplicateEntryException $e) {
			$this->flashMessage("Adding new addon failed.", 'danger');
			$this->redirect('createAddon');
		}
	}



	public function renderEditAddon($addonId)
	{
		$this->template->addon = $this->addon;
	}



	/**
	 * @return EditAddonForm
	 */
	protected function createComponentEditAddonForm()
	{
		if (!$this->addon) $this->error();

		$form = new EditAddonForm($this->formValidators);
		$form->setAddonDefaults($this->addon);
		$form->onSuccess[] = $this->editAddonFormSubmitted;

		return $form;
	}



	/**
	 * @param EditAddonForm
	 */
	public function editAddonFormSubmitted(EditAddonForm $form)
	{
		$values = $form->getValues(TRUE);

		$this->manager->fillAddonWithValues($this->addon, $values, $this->getUser()->getIdentity());
		$this->addons->update($this->addon);

		$this->flashMessage('Addon saved.');
		$this->redirect('Detail:', $this->addon->id);
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



// === Storing addon in session ================================================


	/**
	 * Gets the session key for the addon stored under the current token.
	 * If there is no token, it generates a new one.
	 *
	 * @return string
	 */
	private function getSessionKey()
	{
		if ($this->token === NULL) {
			$this->token = Strings::random();
		}

		return "addon-$this->token";
	}



	/**
	 * Stores the addon object into the session.
	 *
	 * @return void
	 */
	private function storeAddon()
	{
		$key = $this->getSessionKey();
		$this->session[$key] = $this->addon;
	}



	/**
	 * Tries to restore the addon object from session.
	 *
	 * @return void
	 */
	private function restoreAddon()
	{
		if ($this->token !== NULL) {
			$key = $this->getSessionKey();
			if (isset($this->session[$key]) && $this->session[$key] instanceof Addon) {
				$this->addon = $this->session[$key];
			}
		}
	}



	/**
	 * Removes the addon from session.
	 *
	 * @return void
	 */
	private function removeStoredAddon()
	{
		$key = $this->getSessionKey();
		unset($this->session[$key]);
	}
}
