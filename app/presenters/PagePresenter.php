<?php

namespace NetteAddons;

/**
 * @author Jan Cerny
 */
final class PagePresenter extends BasePresenter
{
	/**
	 * @var string
	 * @persistent
	 */
	public $slug;

	/** @var \Nette\Database\Table\ActiveRow|string */
	private $page;

	/** @var Forms\EditPageFormFactory */
	private $editPageForm;



	/**
	 * @param Forms\EditPageFormFactory
	 */
	public function injectForms(Forms\EditPageFormFactory $editPageForm)
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
	 * @return Forms\Form
	 */
	protected function createComponentEditPageForm()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect(':Sign:in', $this->storeRequest());
		}

		$form = $this->editPageForm->create($this->page, $this->getUser()->getIdentity());
		$form->onSuccess[] = $this->editPageFormSubmitted;

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
