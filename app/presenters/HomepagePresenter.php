<?php

namespace NetteAddons;

use NetteAddons\Model\Addons,
	NetteAddons\Model\Reinstall;



class HomepagePresenter extends BasePresenter
{
	const ADDONS_LIMIT = 3;

	/** @var Model\Addons */
	private $addons;

	/** @var Model\Reinstall */
	private $reinstaller;


	/**
	 * @param Model\Addons
	 */
	public function injectAddons(Addons $addons)
	{
		$this->addons = $addons;
	}


	/**
	 * @param Model\Reinstall
	 */
	public function injectReinstaller(Reinstall $reinstaller)
	{
		$this->reinstaller = $reinstaller;
	}



	public function renderDefault()
	{
		$this->template->updatedAddons = $this->addons->findLastUpdated(self::ADDONS_LIMIT);
		$this->template->favoritedAddons = $this->addons->findMostFavorited(self::ADDONS_LIMIT);
		$this->template->usedAddons = $this->addons->findMostUsed(self::ADDONS_LIMIT);
	}



	/**
	 * @secured
	 */
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
