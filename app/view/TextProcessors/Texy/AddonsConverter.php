<?php

namespace NetteAddons\TextProcessors\Texy;


/**
 * Texy parser for addons.
 */
class AddonsConverter extends Converter
{
	public function __construct()
	{
		parent::__construct('addons', 'en', self::HOMEPAGE);
	}

	/**
	 * @return \Texy
	 */
	public function createTexy()
	{
		$texy = parent::createTexy();

		$texy->headingModule->top = 2;

		return $texy;
	}
}
