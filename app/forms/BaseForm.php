<?php

namespace NetteAddons;

use Nette\ComponentModel\IContainer;


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

		$this->buildForm();
	}


	/**
	 * Abstract function which handles the form creation.
	 * @abstract
	 * @return void
	 */
	protected abstract function buildForm();
}
