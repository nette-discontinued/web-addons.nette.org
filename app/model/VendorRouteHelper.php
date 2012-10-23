<?php

namespace NetteAddons\Model;

/**
 * @author Michael Moravec
 */
class VendorRouteHelper extends \Nette\Object
{
	/** @var Addons */
	private $addons;



	/**
	 * @param Addons
	 */
	public function __construct(Addons $addons)
	{
		$this->addons = $addons;
	}

	/**
	 * @param string
	 * @return string|NULL
	 */
	public function filterIn($vendor)
	{
		$row = $this->addons->findByComposerVendor($vendor)->limit(1)->fetch();
		if (!$row) return NULL;
		$addon = Addon::fromActiveRow($row);
		return $addon->composerVendor;
	}

	/**
	 * @param string
	 * @return string|NULL
	 */
	public function filterOut($vendor)
	{
		$row = $this->addons->findByComposerVendor($vendor)->limit(1)->fetch();
		if (!$row) return NULL;
		$addon = Addon::fromActiveRow($row);
		return $addon->composerVendor;
	}
}
