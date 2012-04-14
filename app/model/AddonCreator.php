<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AddonCreator extends Nette\Object
{

	/**
	 * @var Addons
	 */
	private $addons;



	/**
	 * @param \NetteAddons\Model\Addons $addons
	 */
	public function __construct(Addons $addons)
	{
		$this->addons = $addons;
	}



	/**
	 * @param \NetteAddons\Model\Addon $addon
	 */
	public function create(Addon $addon)
	{
		throw new Nette\NotImplementedException;
	}


}
