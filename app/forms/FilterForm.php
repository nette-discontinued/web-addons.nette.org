<?php

namespace NetteAddons\Forms;

use NetteAddons\Model,
	Nette\Application\UI\Form;


/**
 * @author  Patrik Votoček
 */
class FilterForm extends BaseForm
{

	/** @var \NetteAddons\Model\Tags */
	private $tags;



	/**
	 * @param Model\Tags
	 */
	public function __construct(Model\Tags $tags)
	{
		$this->tags = $tags;
		parent::__construct();
	}


	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function buildForm()
	{
		$tags = $this->tags->findMainTags()->fetchPairs('slug', 'name');

		$this->addText('search', 'Search', NULL, 100);
		$this->addSelect('category', 'Category', $tags)
			->setPrompt('Choose category');

		$this->addSubmit('sub', 'Filter');
	}



	/**
	 * @param string
	 * @return FilterForm
	 */
	public function setSearch($search)
	{
		$this['search']->setDefaultValue($search);
		return $this;
	}



	/**
	 * @param string
	 * @return FilterForm
	 */
	public function setCategory($category)
	{
		$this['category']->setDefaultValue($category);
		return $this;
	}

}
