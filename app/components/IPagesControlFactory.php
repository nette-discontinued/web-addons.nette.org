<?php

namespace NetteAddons\Components;

interface IPagesControlFactory
{
	/**
	 * @return PagesControl
	 */
	public function create();
}
