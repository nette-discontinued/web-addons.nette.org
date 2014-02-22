<?php

namespace NetteAddons;


final class SpecialPresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Addons
	 */
	public $addons;


	/**
	 * @param string
	 */
	public function renderSitemap($type = 'html')
	{
		$this->template->addons = $this->addons->findAll();
		$this->template->vendors = $this->addons->findVendors();
		$this->template->categories = $this->tags->findMainTags();

		if ($type == 'xml') {
			$this->setView('sitemap.xml');
		}
	}
}
