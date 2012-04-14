<?php

namespace NetteAddons;

/**
 * @author Jan Marek
 */
class ListPresenter extends BasePresenter
{

	public function renderDefault($tag = NULL, $author = NULL, $search = NULL)
	{
		$addons = $this->context->addons->findAll();

		if ($tag) {
			$addons->where('tag = ?', $tag);
		}

		if ($author) {
			$addons->where('author = ?', $author);
		}

		if ($search) {
			// todo
		}

		$this->template->addons = $addons;
	}

}
