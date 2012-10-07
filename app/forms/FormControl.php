<?php

namespace NetteAddons\Forms;

use Nette;
use Nette\Forms\Form;


/**
 * @author Patrik Votoček
 */
abstract class FormControl extends Nette\Application\UI\Control
{

	/** @var array */
	public $onSuccess;


	/**
	 * @param \Nette\Forms\Form
	 */
	protected function doOnSuccess(Form $form)
	{
		if ($form->valid) {
			callback($this, 'onSuccess')->invokeArgs(func_get_args());
		}
	}

}
