<?php

namespace NetteAddons;

use NetteAddons\Model\Reinstall;


/**
 * @author Patrik Votoček
 * @author Vojtěch Dobeš
 */
final class HomepagePresenter extends BaseListPresenter
{
	const ADDONS_LIMIT = 3;

	/** @var Model\Reinstall */
	private $reinstaller;


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

		$this->template->categories = $categories = $this->tags->findMainTagsWithAddons();
		$this->template->addons = $this->addons->findGroupedByCategories($categories);
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
