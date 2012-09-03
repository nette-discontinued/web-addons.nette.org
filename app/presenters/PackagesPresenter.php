<?php

namespace NetteAddons;

use NetteAddons\Model\Utils\Composer;
use Nette\Application\Responses\JsonResponse;



/**
 * @author Jan Marek
 * @author Jan Tvrdík
 */
class PackagesPresenter extends BasePresenter
{
	public function renderDefault()
	{
		$addons = $this->context->addons->findAll();
		$addons = array_map('NetteAddons\Model\Addon::fromActiveRow', iterator_to_array($addons));

		$packagesJson = Composer::createPackagesJson($addons);
		$this->sendResponse(new JsonResponse($packagesJson));
	}
}
