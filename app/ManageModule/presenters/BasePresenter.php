<?php

namespace NetteAddons\ManageModule;

use Nette\Utils\Strings,
	NetteAddons\Model\Addon,
	NetteAddons\Model\Addons,
	NetteAddons\Model\Facade\AddonManageFacade,
	NetteAddons\Model\Importers\RepositoryImporterManager;



abstract class BasePresenter extends \NetteAddons\BasePresenter
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

	/** @var Addon|NULL from the session. */
	protected $addon;

	/** @var AddonManageFacade */
	protected $manager;

	/** @var RepositoryImporterManager */
	protected $importerManager;

	/** @var Addons */
	protected $addons;



	public function injectManager(AddonManageFacade $manager)
	{
		$this->manager = $manager;
	}



	public function injectImporterManager(RepositoryImporterManager $manager)
	{
		$this->importerManager = $manager;
	}



	public function injectAddonsTable(Addons $addons)
	{
		$this->addons = $addons;
	}


	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection
	 */
	public function checkRequirements($element)
	{
		if (!$this->getUser()->loggedIn) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect('Sign:in', $this->storeRequest());
		}
	}



	protected function startup()
	{
		parent::startup();

		if ($this->token && $this->addonId) {
			$this->error('Parameters token and addonId must not be present at the same time.', 409);
		}

		if ($this->token) {
			$this->addon = $this->manager->restoreAddon($this->getSessionKey());
		} elseif ($this->addonId) {
			$row = $this->addons->find($this->addonId);
			if (!$row) {
				$this->error('Addon not found.');
			}
			$this->addon = Addon::fromActiveRow($row);
		}

		if ($this->addon && !$this->auth->isAllowed($this->addon, 'manage')) {
			$this->error('You are not allowed to manage this addon.', 403);
		}
	}



	/**
	 * Addon importer factory
	 *
	 * @param  string
	 * @return \NetteAddons\Model\IAddonImporter
	 * @throws \NetteAddons\NotSupportedException
	 */
	protected function createAddonImporter($url)
	{
		return $this->importerManager->createFromUrl($url);
	}



	/**
	 * Gets the session key for the addon stored under the current token.
	 * If there is no token, it generates a new one.
	 *
	 * @return string
	 */
	protected function getSessionKey()
	{
		if ($this->token === NULL) {
			$this->token = Strings::random();
		}

		return $this->token;
	}
}
