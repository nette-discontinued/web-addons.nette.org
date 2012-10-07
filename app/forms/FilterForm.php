<?php

namespace NetteAddons\Forms;

use NetteAddons\Model,
	Nette\Application\UI\Form;


/**
 * @author  Patrik Votoček
 */
class FilterForm extends FormControl
{

	/** @var \NetteAddons\Model\Tags */
	private $tagsFacade;



	/**
	 * @param Model\Tags
	 */
	public function __construct(Model\Tags $tagsFacade)
	{
		parent::__construct();
		$this->tagsFacade = $tagsFacade;
	}


	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentForm()
	{
		$tags = $this->tagsFacade->findMainTags()->fetchPairs('slug', 'name');

		$form = new Form;

		$form->addText('search', 'Search', 40, 100);
		$form->addSelect('category', 'Category', $tags)
			->setPrompt('Choose category');

		$form->addSubmit('sub', 'Filter');

		$form->onSuccess[] = callback($this, 'process');

		return $form;
	}



	/**
	 * @param \Nette\Application\UI\Form
	 */
	public function process(Form $form)
	{
		$values = $form->values;
		$this->doOnSuccess($form, $values->search, $values->category);
	}



	/**
	 * @param string
	 * @return FilterForm
	 */
	public function setSearch($search)
	{
		$this['form-search']->setDefaultValue($search);
		return $this;
	}



	/**
	 * @param string
	 * @return FilterForm
	 */
	public function setCategory($category)
	{
		$this['form-category']->setDefaultValue($category);
		return $this;
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/templates/FilterForm.latte');
		$this->template->render();
	}

}
