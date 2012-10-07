<?php

namespace NetteAddons;

use NetteAddons\Model\Addons;
use NetteAddons\Model\AddonVotes;

/**
 * @author Jan Marek
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

		$this->template->addonVotes = callback($this->addonVotes, 'calculatePopularity');
		$this->template->addons = $addons;
	}



	protected function createComponentFilterForm()
	{
		$form = new FilterForm($this->context->tags);
		$form->onSuccess[] = array($this, 'filterFormSubmitted');
		$form->setDefaults(array(
			'search' => $this->getParameter('search'),
			'tag' => $this->getParameter('tag'),
		));

		return $form;
	}



	public function filterFormSubmitted(FilterForm $form)
	{
		$values = $form->getValues();

		$this->redirect('default', array(
			'search' => $values->search,
			'tag' => $values->tag,
		));
	}

}
