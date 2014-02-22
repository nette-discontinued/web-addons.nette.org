<?php

namespace NetteAddons\Forms;


abstract class BaseForm extends Form
{
	public function __construct()
	{
		parent::__construct();

		$this->buildForm();
	}


	/**
	 * @return void
	 */
	protected abstract function buildForm();
}
