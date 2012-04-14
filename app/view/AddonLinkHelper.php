<?php

namespace NetteAddons;

use Nette\Utils\Html;

/**
 * @author Jan Marek
 */
class AddonLinkHelper
{

	private $control;

	public function __construct($control)
	{
		$this->control = $control;
	}

	public function __invoke($addon)
	{
		$href = $this->control->link('Detail:', array('id' => $addon->id));
		return Html::el('a')->href($href)->setText($addon->name);
	}

}
