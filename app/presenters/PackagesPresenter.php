<?php

namespace NetteAddons;

use Nette\Application\Responses\JsonResponse;

/**
 * @author Jan Marek
 */
class PackagesPresenter extends BasePresenter
{

	public function renderDefault()
	{
		$packages = $this->context->addons->findAll();
		$packages = array_map('NetteAddons\Model\Addon::fromActiveRow', iterator_to_array($packages));
		$data = $this->context->composer->createPackages($packages);

		$this->sendResponse(new JsonResponse($data));
	}

}
