<?php

namespace NetteAddons;



class FilterForm extends BaseForm
{

	/** @var \NetteAddons\Model\Tags */
	private $tagsRepository;



	public function __construct(Model\Tags $tagsRepository)
	{
		$this->tagsRepository = $tagsRepository;
		parent::__construct();
	}


	protected function buildForm()
	{
		$tags = $this->tagsRepository->findMainTags()->fetchPairs('id', 'name');

		$this->addText('search', 'Search', 40, 100);
		$this->addSelect('tag', 'Tag', $tags)->setPrompt('Choose tag');

		$this->addSubmit('s', 'Filter');
	}

}
