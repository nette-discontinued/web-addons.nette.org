<?php

namespace NetteAddons;

use Nette\Application\UI\Form,
	NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVotes;

/**
 * @author Jan Marek
 * @author Patrik Votoček
 */
class ListPresenter extends BasePresenter
{

	/** @var Model\Addons */
	private $addons;

	/** @var Model\AddonVotes */
	private $addonVotes;



	public function injectAddons(Addons $addons)
	{
		$this->addons = $addons;
	}



	public function injectAddonsVotes(AddonVotes $addonVotes)
	{
		$this->addonVotes = $addonVotes;
	}



	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->addonVotes = callback($this->addonVotes, 'calculatePopularity');
	}



	/**
	 * @param string|NULL
	 */
	public function actionDefault($vendor = NULL)
	{
		if ($vendor == NULL) {
			$this->redirect(':Homepage:');
		}
	}



	/**
	 * @param string
	 */
	public function renderDefault($vendor)
	{
		$this->template->vendor = $vendor;
		$this->template->addons = $this->addons->findByComposerVendor($vendor);
	}



	public function actionMine()
	{
		if (!$this->getUser()->loggedIn) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect('Sign:in', $this->storeRequest());
		}
	}



	public function renderMine()
	{
		$this->template->addons = $this->addons->findByUser($this->user->id);
	}

}
