<?php

namespace NetteAddons;
use NetteAddons\Model\AddonVotes;

/**
 * @author Jan Marek
 */
class ListPresenter extends BasePresenter
{

	/** @var AddonVotes */
	private $addonVotes;



	public function injectAddons(AddonVotes $addonVotes)
	{
		$this->addonVotes = $addonVotes;
	}



	public function renderDefault($tag = NULL, $author = NULL, $search = NULL)
	{
		$addonRepository = $this->context->addons;
		$addons = $addonRepository->findAll();

		if ($tag) {
			$addonRepository->filterByTag($addons, $tag);
		}

		if ($author) {
			$addons->where('user = ?', $author);
		}

		if ($search) {
			$addonRepository->filterByString($addons, $search);
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
