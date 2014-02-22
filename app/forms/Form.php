<?php

namespace NetteAddons\Forms;

use Nette\Forms\Rendering\DefaultFormRenderer;


class Form extends \Nette\Application\UI\Form
{
	public function __construct()
	{
		parent::__construct();

		$renderer = $this->getRenderer();
		if ($renderer instanceof DefaultFormRenderer) {
			$renderer->wrappers['form']['container'] = 'div class=form';
			$renderer->wrappers['controls']['container'] = NULL;
			$renderer->wrappers['pair']['container'] = 'div class=controls';
			$renderer->wrappers['control']['container'] = NULL;
			$renderer->wrappers['label']['container'] = NULL;
		}
	}
}
