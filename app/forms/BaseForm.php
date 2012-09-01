<?php

namespace NetteAddons;

use Nette\Application\UI\Form,
	Nette\ComponentModel\IContainer;


/**
 * Base class for all forms.
 *
 * Handles the form rendering using form templates.
 */
abstract class BaseForm extends Form
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

		$this->buildForm();
	}


	/**
	 * Abstract function which handles the form creation.
	 * @abstract
	 * @return void
	 */
	protected abstract function buildForm();
}
