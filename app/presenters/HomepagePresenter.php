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
	}



	public function handleReinstall()
	{
		$this->reinstaller->recreateDatabase();

		$this->flashMessage('Fuk yea!');
		$this->redirect('this');
	}
}
