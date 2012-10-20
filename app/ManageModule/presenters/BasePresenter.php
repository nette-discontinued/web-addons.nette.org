<?php

namespace NetteAddons\ManageModule;

use Nette\Utils\Strings,
	NetteAddons\Model\Addon,
	NetteAddons\Model\Addons,
	NetteAddons\Model\Facade\AddonManageFacade,
	NetteAddons\Model\Importers\RepositoryImporterManager;



class BasePresenter extends \NetteAddons\BasePresenter
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

	/** @var AddonManageFacade */
	protected $manager;

	/** @var RepositoryImporterManager */
	protected $importerManager;

	/** @var Addons */
	protected $addons;

	/** @var Addon|NULL from the session. */
	protected $addon;



	public function injectImporterManager(RepositoryImporterManager $manager)
	{
		$this->importerManager = $manager;
	}



	public function injectAddonsTable(Addons $addons)
	{
		$this->addons = $addons;
	}



	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect('Sign:in', $this->storeRequest());
		}

		if ($this->token && $this->addonId) {
			$this->error('Parameters token and addonId must not be present at the same time.', 409);
		}

		$this->manager = $this->createAddonManageFacade();

		if ($this->token) {
			$this->addon = $this->manager->restoreAddon($this->getSessionKey());
		} elseif ($this->addonId) {
			$row = $this->addons->find($this->addonId);
			if (!$row) $this->error();
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



	protected function createAddonManageFacade()
	{
		$currentUrl = $this->getHttpRequest()->getUrl();
		return new AddonManageFacade(
			$this->getSession(),
			$this->context->parameters['uploadDir'],
			$currentUrl->getHostUrl() . rtrim($currentUrl->getBasePath(), '/') . $this->context->parameters['uploadUri']
		);
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

		return "addon-$this->token";
	}
}
