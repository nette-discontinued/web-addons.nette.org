<?php

namespace NetteAddons;


/**
 * @author Patrik Votoček
 */
class Portal extends \Nette\Object
{
	const VERSION = '1.0-dev';

	public function __construct()
	{
		throw new \NetteAddons\StaticClassException;
	}
}
