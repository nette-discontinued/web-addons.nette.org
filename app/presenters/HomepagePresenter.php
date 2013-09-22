<?php

namespace NetteAddons;

use NetteAddons\Model\DevelopmentUtils;


/**
 * @author Patrik Votoček
 * @author Vojtěch Dobeš
 */
final class HomepagePresenter extends BaseListPresenter
{
	const ADDONS_LIMIT = 3;

	/**
	 * @var Model\DevelopmentUtils
	 * @inject
	 */
	public $developmentUtils;



	public function renderDefault()
	{
		$ignoreDeleted = $this->auth->isAllowed('addon', 'delete');

		$this->template->updatedAddons = $this->addons->findLastUpdated(self::ADDONS_LIMIT, $ignoreDeleted);
		$this->template->favoritedAddons = $this->addons->findMostFavorited(self::ADDONS_LIMIT, $ignoreDeleted);
		$this->template->usedAddons = $this->addons->findMostUsed(self::ADDONS_LIMIT, $ignoreDeleted);

		$this->template->categories = $categories = $this->tags->findMainTagsWithAddons();
		$this->template->addons = $this->addons->findGroupedByCategories($categories, $ignoreDeleted);
	}



	/**
	 * @secured
	 */
	public function handleReinstall()
	{
		if ($this->context->parameters['productionMode'] !== FALSE) {
			$this->error();
		}

		$this->developmentUtils->recreateDatabase();

		$this->flashMessage('Fuk yea!');
		$this->redirect('this');
	}



	/**
	 * @secured
	 */
	public function handleRandomDownloadAndInstalls()
	{
		if ($this->context->parameters['productionMode'] !== FALSE) {
			$this->error();
		}

		$this->developmentUtils->generateRandomDownloadsAndInstalls();

		$this->flashMessage('Fuk yea!');
		$this->redirect('this');
	}
}
