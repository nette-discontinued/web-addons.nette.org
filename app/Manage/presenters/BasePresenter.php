<?php

namespace NetteAddons\Manage;

use NetteAddons\Model\Addon;


abstract class BasePresenter extends \NetteAddons\BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Facade\AddonManageFacade
	 */
	public $manager;

	/**
	 * @inject
	 * @var \NetteAddons\Model\Importers\RepositoryImporterManager
	 */
	public $importerManager;

	/**
	 * @inject
	 * @var \NetteAddons\Model\Addons
	 */
	public $addons;

	/**
	 * @persistent
	 * @var string token used for storing addon in session
	 */
	public $token;

	/**
	 * @persistent
	 * @var int
	 */
	public $addonId;

	/** @var \NetteAddons\Model\Addon|NULL from the session. */
	protected $addon;


	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection
	 */
	public function checkRequirements($element)
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect(':Sign:in', $this->storeRequest());
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
			$deleted = $this->auth->isAllowed('addon', 'delete');
			$row = $this->addons->find($this->addonId, $deleted);

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
