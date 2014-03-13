<?php

namespace NetteAddons;

final class PagePresenter extends BasePresenter
{
	const SOURCE_FILE_PATH_MASK = '%s/page/%s.texy';

	/**
	 * @inject
	 * @var \NetteAddons\TextProcessors\PageProcessor
	 */
	public $pageProcessor;

	/** @var string */
	private $dataPath;


	/**
	 * @param string
	 */
	public function __construct($dataPath)
	{
		parent::__construct();

		$this->dataPath = $dataPath;
	}


	/**
	 * @param string
	 */
	public function renderDefault($slug)
	{
		$this->checkSlugExist($slug);

		$content = file_get_contents($this->getFilePath($slug));
		$data = $this->pageProcessor->process($content, 'page/' . $slug);

		$this->template->title = $data['title'];
		$this->template->content = $data['content'];
		$this->template->toc = $data['toc'];
	}

	/**
	 * @param string
	 */
	private function checkSlugExist($slug)
	{
		$path = $this->getFilePath($slug);
		if (!file_exists($path)) {
			$this->error('Page does not exist');
		}
	}

	/**
	 * @param string
	 * @return string
	 */
	private function getFilePath($slug)
	{
		return sprintf(self::SOURCE_FILE_PATH_MASK, $this->dataPath, $slug);
	}
}
