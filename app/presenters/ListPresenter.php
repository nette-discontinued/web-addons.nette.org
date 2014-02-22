<?php

namespace NetteAddons;


final class ListPresenter extends BaseListPresenter
{
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
