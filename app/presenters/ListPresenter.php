<?php

namespace NetteAddons;

use Nette\Application\UI\Form;
use NetteAddons\Model\Addons;
use NetteAddons\Model\AddonVotes;

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



	public function renderDefault($tag = NULL, $author = NULL, $search = NULL)
	{
		$addons = $this->addons->findAll();

		if ($tag) {
			$this->addons->filterByTag($addons, $tag);
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
	 * @return FilterForm
	 */
	protected function createComponentFilterForm()
	{
		$control = new FilterForm($this->tags);
		$control->setSearch($this->getParameter('search'))
			->setCategory($this->getParameter('tag'));

		$control->onSuccess[] = callback($this, 'applyFilter');

		return $control;
	}



	/**
	 * @param \Nette\Application\UI\Form
	 * @param string
	 * @param string
	 */
	public function applyFilter(Form $form, $search, $category)
	{
		$this->redirect('default', array(
			'search' => $search,
			'tag' => $category,
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

}
