<?php

namespace NetteAddons;

/**
 * @author Jan Cerny
 */
class PagePresenter extends BasePresenter
{
	/**
	 * @var string
	 * @persistent
	 */
	public $slug;

	/** @var \Nette\Database\Table\ActiveRow|string */
	private $page;

	/** @var Forms\EditPageForm */
	private $editPageForm;



	/**
	 * @param Forms\EditPageForm
	 */
	public function injectForms(Forms\EditPageForm $editPageForm)
	{
		$this->editPageForm = $editPageForm;
	}



	protected function startup()
	{
		parent::startup();
		$this->page = $this->pages->findOneBySlug($this->slug);
		if (!$this->page) {
			$this['subMenu']->setPage($this->slug);
			$this->error();
		}
		$this['subMenu']->setPage($this->page);
	}



	/**
	 * @param string
	 */
	public function renderDefault($slug)
	{
		$this->template->page = $this->page;
		$description = $this->textPreprocessor->processTexyContent($this->page->content);

		$this->template->content = $description['content'];
		$this->template->toc = $description['toc'];
	}



	/**
	 * @return Forms\EditPageForm
	 */
	protected function createComponentEditPageForm()
	{
		$form = $this->editPageForm;

		$form->setUser($this->getUser()->identity);
		$form->setPage($this->page);

		$form->onSuccess[] = $this->editPageFormSubmitted;

		return $form;
	}



	/**
	 * @param Forms\EditPageForm
	 */
	public function editPageFormSubmitted(Forms\EditPageForm $form)
	{
		if ($form->valid) {
			$this->flashMessage('Page saved.');
			$this->redirect('default');
		}
	}



	/**
	 * @param string
	 */
	public function renderEdit($slug)
	{
		$this->template->page = $this->page;
	}

}
