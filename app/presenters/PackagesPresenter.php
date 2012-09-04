<?php

namespace NetteAddons;

use NetteAddons\Model\Addons;
use NetteAddons\Model\Utils\Composer;
use Nette\Application\Responses\JsonResponse;



/**
 * @author Jan Marek
 * @author Jan Tvrdík
 */
class PackagesPresenter extends BasePresenter
{
	/** @var Addons */
	private $addons;



	public function injectAddons(Addons $addons)
	{
		$this->addons = $addons;
	}



	public function renderDefault()
	{
		$addons = $this->addons->findAll();
		$addons = array_map('NetteAddons\Model\Addon::fromActiveRow', iterator_to_array($addons));

		$packagesJson = Composer::createPackagesJson($addons);
		$this->sendResponse(new JsonResponse($packagesJson));
	}
}
