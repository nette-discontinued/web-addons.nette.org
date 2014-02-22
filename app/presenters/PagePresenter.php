<?php

namespace NetteAddons;


final class PagePresenter extends BasePresenter
{
	/**
	 * @inject
	 * @var \NetteAddons\Forms\EditPageFormFactory
	 */
	public $editPageForm;

	/**
	 * @persistent
	 * @var string
	 */
	public $slug;

	/** @var \Nette\Database\Table\ActiveRow|string */
	private $page;


	protected function startup()
	{
		parent::startup();

		if (!$this->slug) {
			$this->error();
		}

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
	 * @return Forms\Form
	 */
	protected function createComponentEditPageForm()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect(':Sign:in', $this->storeRequest());
		}

		$form = $this->editPageForm->create($this->page, $this->getUser()->getIdentity());
		$form->onSuccess[] = array($this, 'editPageFormSubmitted');

		return $form;
	}


	/**
	 * @param Forms\Form
	 */
	public function editPageFormSubmitted(Forms\Form $form)
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
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect(':Sign:in', $this->storeRequest());
		}

		$this->template->page = $this->page;
	}
}
