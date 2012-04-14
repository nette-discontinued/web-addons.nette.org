<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AddonVersion extends Nette\Object
{

	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var array|string[]
	 */
	public $require = array();

	/**
	 * @var array|string[]
	 */
	public $suggest = array();

	/**
	 * @var array|string[]
	 */
	public $provide = array();

	/**
	 * @var array|string[]
	 */
	public $replace = array();

	/**
	 * @var array|string[]
	 */
	public $conflict = array();

	/**
	 * @var array|string[]
	 */
	public $recommend = array();

}
