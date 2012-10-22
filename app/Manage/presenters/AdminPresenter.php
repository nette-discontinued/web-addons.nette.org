<?php

namespace NetteAddons\Manage;

use NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVotes;


/**
 * @author Patrik Votoček
 */
final class AdminPresenter extends \NetteAddons\BasePresenter
{
	/** @var \NetteAddons\Model\Addons */
	private $addons;

	/** @var \NetteAddons\Model\AddonVotes */
	private $addonVotes;


	
	/**
	 * @param \NetteAddons\Model\Addons
	 * @param \NetteAddons\Model\AddonVotes
	 */
	public function injectModel(Addons $addons, AddonVotes $addonVotes)
	{
		$this->addons = $addons;
		$this->addonVotes = $addonVotes;
	}


	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection
	 */
	public function checkRequirements($element)
	{
		$user = $this->getUser();
		if (!$user->isLoggedIn()) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect(':Sign:in', $this->storeRequest());
		} elseif (!$this->user->isInRole('moderators') && !$this->user->isInRole('administrators')) {
			$this->error('This section is only for admins and moderators.', 403);
		}
	}



	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->addonVotes = callback($this->addonVotes, 'calculatePopularity');
	}



	public function actionDeleted()
	{
		if (!$this->auth->isAllowed('addon', 'delete')) {
			$this->error('You are not allowed to list deleted addons.', 403);
		}
	}



	public function renderDeleted()
	{
		$this->template->addons = $this->addons->findDeleted();
	}

}
