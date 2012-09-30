<?php

namespace NetteAddons;

use NetteAddons\Model\Addons;
use NetteAddons\Model\Reinstall;



class HomepagePresenter extends BasePresenter
{
	/** @var Addons */
	private $addons;

	/** @var Reinstall */
	private $reinstaller;



	public function injectAddons(Addons $addons)
	{
		$this->addons = $addons;
	}



	public function injectReinstaller(Reinstall $reinstaller)
	{
		$this->reinstaller = $reinstaller;
	}



	public function renderDefault()
	{
		$this->template->updatedAddons = $this->addons->findAll()
			->order('updatedAt DESC')->limit(3);
		$this->template->favoritedAddons = $this->addons->findAll()->group('id')
			->order('SUM(addons_vote:vote) DESC')->limit(3);
		$this->template->usedAddons = $this->addons->findAll()->group('id')
			->order('SUM(totalDownloadsCount + totalInstallsCount) DESC')->limit(3);
	}



	public function handleReinstall()
	{
		if ($this->context->parameters['productionMode']) {
			$this->error();
		}

		$this->reinstaller->recreateDatabase();

		$this->flashMessage('Fuk yea!');
		$this->redirect('this');
	}
}
