<?php

namespace NetteAddons\Forms;

use NetteAddons\Model\Tags;


class FilterForm extends BaseForm
{
	/** @var \NetteAddons\Model\Tags */
	private $tags;

	/** @var array */
	private $tagsPairs;


	public function __construct(Tags $tags)
	{
		$this->tags = $tags;

		parent::__construct();
	}


	protected function buildForm()
	{
		$this->tagsPairs = $this->tags->findMainTags()->fetchPairs('slug', 'name');

		$this->addText('search', 'Search', NULL, 100);
		$this->addSelect('category', 'Category', $this->tagsPairs)
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


	/**
	 * @return string
	 */
	public function getCategory()
	{
		$value = $this['category']->getValue();
		return $value ? $this->tagsPairs[$value] : NULL;
	}
}
