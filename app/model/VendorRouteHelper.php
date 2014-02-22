<?php

namespace NetteAddons\Model;


class VendorRouteHelper extends \Nette\Object
{
	/** @var \NetteAddons\Model\Addons */
	private $addons;


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

		if (!$row) {
			return NULL;
		}

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

		if (!$row) {
			return NULL;
		}

		$addon = Addon::fromActiveRow($row);

		return $addon->composerVendor;
	}
}
