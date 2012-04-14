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
	 * @var string
	 */
	public $vendorName;

	/**
	 * @var string
	 */
	public $shortDescription;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $repository;

	/**
	 * @var array|AddonVersion[]
	 */
	public $versions = array();

	/**
	 * @var array|string[]
	 */
	public $tags = array();

}
