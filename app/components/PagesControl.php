<?php

namespace NetteAddons\Components;

use NetteAddons\TextProcessors\PageProcessor;


class PagesControl extends \Nette\Application\UI\Control
{

	const FILE_PATH_MASK = '%s/meta/menu.texy';

	/** @var \NetteAddons\TextProcessors\PageProcessor */
	private $pageProcessor;

	/** @var string */
	private $dataPath;


	/**
	 * @param \NetteAddons\TextProcessors\PageProcessor
	 * @param string
	 */
	public function __construct(PageProcessor $pageProcessor, $dataPath)
	{
		parent::__construct();

		$this->pageProcessor = $pageProcessor;
		$this->dataPath = $dataPath;
	}


	public function render()
	{
		$path = sprintf(self::FILE_PATH_MASK, $this->dataPath);
		if (!file_exists($path)) {
			return;
		}

		$content = file_get_contents($path);
		$data = $this->pageProcessor->process($content);

		echo $data['content'];
	}
}
