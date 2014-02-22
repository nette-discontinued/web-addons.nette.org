<?php

namespace NetteAddons\Components;

use Nette;
use NetteAddons\Model\Tags;


class CategoriesControl extends \Nette\Application\UI\Control
{
	/** * @var \NetteAddons\Model\Tags */
	private $tags;


	public function __construct(Tags $tags)
	{
		parent::__construct();

		$this->tags = $tags;
	}


	public function render()
	{
		$this->template->categories = $this->tags->findMainTagsWithAddons();
		$this->template->active = $this->presenter->getParameter('category');

		$this->template->setFile(__DIR__ . '/Categories.latte');
		$this->template->render();
	}
}
