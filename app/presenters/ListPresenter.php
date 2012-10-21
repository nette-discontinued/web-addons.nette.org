<?php

namespace NetteAddons;

use Nette\Application\UI\Form,
	NetteAddons\Model\Addons,
	NetteAddons\Model\AddonVotes;

/**
 * @author Jan Marek
 * @author Patrik Votoček
 */
class ListPresenter extends BasePresenter
{

	/** @var Model\Addons */
	private $addons;

	/** @var Model\AddonVotes */
	private $addonVotes;



	public function injectAddons(Addons $addons)
	{
		$this->addons = $addons;
	}



	public function injectAddonsVotes(AddonVotes $addonVotes)
	{
		$this->addonVotes = $addonVotes;
	}



	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->addonVotes = callback($this->addonVotes, 'calculatePopularity');
	}


	/**
	 * @param string
	 * @param string
	 * @param string
	 * @param id
	 */
	public function renderDefault($category = NULL, $search = NULL, $tag = NULL, $author = NULL)
	{
		$addons = $this->addons->findAll();

		if ($category) {
			$categoryId = $this->tags->findOneBySlug($category);
			if (!$categoryId) {
				$this->flashMessage('Invalid category');
				$this->redirect('this', array('category' => NULL));
			}
			$this->addons->filterByTag($addons, $categoryId);
		}

		if ($tag) {
			$tagId = $this->tags->findOneBySlug($tag);
			if (!$tagId) {
				$this->flashMessage('Invalid tag');
				$this->redirect('this', array('tag' => NULL));
			}
			$this->addons->filterByTag($addons, $tagId);
		}

		if ($author) {
			$addons->where('user = ?', $author);
		}

		if ($search) {
			$this->addons->filterByString($addons, $search);
		}

		$this->template->addons = $addons;
	}



	/**
	 * @return Forms\FilterForm
	 */
	protected function createComponentFilterForm()
	{
		$control = new Forms\FilterForm($this->tags);
		$control->setSearch($this->getParameter('search'))
			->setCategory($this->getParameter('category'));

		$control->onSuccess[] = $this->applyFilter;

		return $control;
	}



	/**
	 * @param Forms\FilterForm
	 */
	public function applyFilter(Forms\FilterForm $form)
	{
		$values = $form->values;
		$this->redirect('default', array(
			'search' => $values->search,
			'category' => $values->category,
		));
	}



	public function actionMine()
	{
		if (!$this->getUser()->loggedIn) {
			$this->flashMessage('Please sign in to continue.');
			$this->redirect('Sign:in', $this->storeRequest());
		}
	}



	public function renderMine()
	{
		$this->template->addons = $this->addons->findByUser($this->user->id);
	}



	public function renderByVendor($vendor)
	{
		$this->template->vendor = $vendor;
		$this->template->addons = $this->addons->findByComposerVendor($vendor);
	}



	public function actionDeleted()
	{
		if (!$this->auth->isAllowed('addon', 'delete')) {
			$this->error('You are not allowed to list deleted addons.', 403);
		}
	}



	public function renderDeleted()
	{
		$this->template->addons = $this->addons->findDeleted();
	}

}
