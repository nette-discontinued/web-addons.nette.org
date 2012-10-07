<?php

namespace NetteAddons;

use Nette;
use NetteAddons\Model\Tags;



/**
 * @author Patrik Votoček
 */
class CategoriesControl extends Nette\Application\UI\Control
{

	/** @var Tags */
	private $tags;



	/**
	 * @param Model\Tags
	 */
	public function __construct(Tags $tags)
	{
		parent::__construct();
		$this->tags = $tags;
	}



	public function render()
	{
		$this->template->categories = $this->tags->findMainTags();

		$this->template->setFile(__DIR__ . '/Categories.latte');
		$this->template->render();
	}

}
