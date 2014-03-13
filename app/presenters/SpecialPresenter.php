<?php

namespace NetteAddons;

use Nette\Utils\Finder;
use Nette\Utils\Strings;

final class SpecialPresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Model\Addons
	 */
	public $addons;

	/**
	 * @inject
	 * @var \NetteAddons\TextProcessors\PageProcessor
	 */
	public $pageProcessor;

	/** @var string */
	private $pagesDataPath;


	/**
	 * @param string
	 */
	public function __construct($pagesDataPath)
	{
		parent::__construct();

		$this->pagesDataPath = realpath($pagesDataPath . '/page');
	}


	/**
	 * @param string
	 */
	public function renderSitemap($type = 'html')
	{
		$this->template->addons = $this->addons->findAll();
		$this->template->vendors = $this->addons->findVendors();
		$this->template->categories = $this->tags->findMainTags();

		$pages = array();
		foreach (Finder::findFiles('*.texy')->from($this->pagesDataPath) as $file) {
			/** @var \SplFileInfo $file */
			$slug = Strings::substring($file->getRealPath(), Strings::length($this->pagesDataPath) + 1, -5);
			$data = $this->pageProcessor->process(file_get_contents($file->getRealPath()));
			$createdAt = new \DateTime;
			$createdAt->setTimestamp($file->getMTime());
			$pages[] = (object) array(
				'slug' => $slug,
				'name' => $data['title'],
				'createdAt' => $createdAt,
			);
		}

		$this->template->pages = $pages;


		if ($type == 'xml') {
			$this->setView('sitemap.xml');
		}
	}
}
