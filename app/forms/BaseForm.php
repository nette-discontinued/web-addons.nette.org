<?php

namespace NetteAddons\Forms;

use Nette\Application\UI\Presenter;


/**
 * Base class for all forms.
 *
 * Handles the form rendering using form templates.
 */
abstract class BaseForm extends Form
{
	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 *
	 * @param  \Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);
		if ($presenter instanceof Presenter) {
			$this->buildForm();
		}
	}


	/**
	 * Abstract function which handles the form creation.
	 * @abstract
	 * @return void
	 */
	abstract protected function buildForm();
}
