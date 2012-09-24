<?php

namespace NetteAddons;

use NetteAddons\Model\Addon;
use NetteAddons\Model\Addons;
use NetteAddons\Model\AddonVersion;
use NetteAddons\Model\AddonVersions;
use NetteAddons\Model\IAddonImporter;
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

	/** @var AddonVersions */
	private $versions;

	/** @var FormValidators */
	private $formValidators;

	/** @var Addon|NULL from the session. */
	private $addon;



	public function injectSession(Session $session)
	{
		$this->session = $session->getSection(__CLASS__);
	}



	public function injectAddons(Addons $addons)
	{
		$this->addons = $addons;
	}



	public function injectVersions(AddonVersions $versions)
	{
		$this->versions = $versions;
	}



	public function injectFormValidators(FormValidators $formValidators)
	{
		$this->formValidators = $formValidators;
	}



	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect('Sign:in', $this->storeRequest());
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

		if ($this->addon && !$this->auth->isAllowed($this->addon, 'manage')) {
			$this->error('You are not allowed to manage this addon.', 403);
		}

		$this->manager = $this->createAddonManageFacade();
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
			$form->removeComponent($form['repository']);
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

		$imported = (bool) $this->addon->repositoryHosting; // TODO: remove

		$values = $form->getValues(TRUE);
		if (!empty($values['repository'])) {
			$values['repository'] = $this->manager->tryNormalizeRepoUrl($values['repository'], $values['repositoryHosting']);
		}

		$this->manager->fillAddonWithValues($this->addon, $values, $this->getUser()->getIdentity());
		$this->storeAddon();

		if ($imported) {
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
			$url = $this->manager->tryNormalizeRepoUrl($url, $hosting);
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
	public function renderCreateVersion($addonId = NULL)
	{

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
			$version = $this->manager->addVersionFromValues($this->addon, $values, $this->getUser()->getIdentity());

		} catch (\NetteAddons\IOException $e) {
			$form['archive']->addError('Uploading file failed.');
			return;
		}

		if ($this->addonId) { // TODO: better
			$this->versions->add($version);
			$this->flashMessage('Version created.');
			$this->redirect('Detail:', $this->addonId);

		} else {
			$this->storeAddon();
			$this->redirect('finish');
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

		try {
			$importer = $this->createAddonImporter($this->addon->repository);
			$result = $this->manager->updateVersions($this->addon, $importer, $this->getUser()->getIdentity());

		} catch (\NetteAddons\IOException $e) {
			$this->flashMessage('Version importing failed. Try again later.', 'error');
			$this->redirect('Detail:', $this->addon->id);
		}

		if (count($result['conflicted']) === 0) {
			if (count($result['new']) === 0) {
				$this->flashMessage('Nothing new…');
			} else {
				try {
					foreach ($result['new'] as $version) {
						$this->versions->add($version);
					}
					$this->flashMessage('New versions have been imported.');
				} catch (\PDOException $e) {
					$this->flashMessage('Version importing failed. Try again later.', 'error');
				}
			}

			$this->redirect('Detail:', $this->addon->id);
		} else {
			$this->flashMessage('There is a conflict!');
			$this->redirect('Detail:', $this->addon->id);


		}

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

		if (!empty($values['repository'])) {
			$values['repository'] = $this->manager->tryNormalizeRepoUrl($values['repository'], $values['repositoryHosting']);
		}

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
		$url = new \Nette\Http\Url($url);
		return $this->getContext()->repositoryImporterFactory->createFromUrl($url);
	}



	private function createAddonManageFacade()
	{
		$currentUrl = $this->getHttpRequest()->getUrl();
		return new AddonManageFacade(
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
