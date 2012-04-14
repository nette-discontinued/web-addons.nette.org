<?php

namespace NetteAddons;

/**
 * @author Jan Marek
 */
class ListPresenter extends BasePresenter
{

	public function renderDefault($tag = NULL, $author = NULL, $search = NULL)
	{
		$addonRepository = $this->context->addons;
		$addons = $addonRepository->findAll();

		if ($tag) {
			$addonRepository->filterByTag($addons, $tag);
		}

		if ($author) {
			$addons->where('user = ?', $author);
		}

		if ($search) {
			// todo
		}

		$this->template->addons = $addons;
	}

}
