<?php

namespace NetteAddons;

/**
 * @author Patrik Votoček
 */
final class SpecialPresenter extends BasePresenter
{
	/** @var Model\Addons */
	private $addons;



	/**
	 * @param Model\Addons
	 */
	public function injectModel(Model\Addons $addons)
	{
		$this->addons = $addons;
	}



	/**
	 * @param string output type
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
