<?php

namespace NetteAddons\Manage;

use Nette\Utils\Strings,
	NetteAddons\Model\Addon,
	NetteAddons\Model\Addons,
	NetteAddons\Model\Facade\AddonManageFacade,
	NetteAddons\Model\Importers\RepositoryImporterManager;


/**
 * @author Patrik Votoček
 */
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

	/** @var \NetteAddons\Model\Facade\AddonManageFacade */
	protected $manager;

	/** @var \NetteAddons\Model\Importers\RepositoryImporterManager */
	protected $importerManager;

	/** @var \NetteAddons\Model\Addons */
	protected $addons;

	/** @var \NetteAddons\Model\Addon|NULL from the session. */
	protected $addon;



	/**
	 * @param \NetteAddons\Model\Facade\AddonManageFacade
	 */
	public function injectManager(AddonManageFacade $manager)
	{
		$this->manager = $manager;
	}



	/**
	 * @param \NetteAddons\Model\Importers\RepositoryImporterManager $manager
	 */
	public function injectImporterManager(RepositoryImporterManager $manager)
	{
		$this->importerManager = $manager;
	}



	/**
	 * @param \NetteAddons\Model\Addons $addons
	 */
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
			$this->addon = $this->manager->restoreAddon($this->token);
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
}
