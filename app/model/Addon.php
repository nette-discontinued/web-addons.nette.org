<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Addon extends Nette\Object
{

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var array|AddonVersion[]
	 */
	public $versions = array();

}
