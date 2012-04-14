<?php

namespace NetteAddons;

use Nette\Utils\Html;

/**
 * @author Jan Marek
 */
class UserLinkHelper
{

	private $control;

	public function __construct($control)
	{
		$this->control = $control;
	}

	public function __invoke($user)
	{
		$href = $this->control->link('People:detail', array('id' => $user->id));
		return Html::el('a')->href($href)->setText($user->name);
	}

}
