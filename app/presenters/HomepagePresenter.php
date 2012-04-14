<?php

namespace NetteAddons;

class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$addons = $this->context->addons;

		$this->template->updatedAddons = $addons->findAll()->order('updated_at DESC')->limit(3);

		$this->template->categories = $this->context->tags->findMainTags();
	}

}
