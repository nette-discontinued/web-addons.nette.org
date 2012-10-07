<?php

namespace NetteAddons\Forms;

use Nette,
	Nette\ComponentModel\IContainer;


/**
 * Base class for all forms.
 *
 * Handles the form rendering using form templates.
 */
class Form extends Nette\Application\UI\Form
{
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$renderer = $this->getRenderer();
		if ($renderer instanceof \Nette\Forms\Rendering\DefaultFormRenderer) {
			$renderer->wrappers['form']['container'] = 'div class=form';
			$renderer->wrappers['controls']['container'] = NULL;
			$renderer->wrappers['pair']['container'] = 'div class=controls';
			$renderer->wrappers['control']['container'] = NULL;
			$renderer->wrappers['label']['container'] = NULL;
		}
	}
}
