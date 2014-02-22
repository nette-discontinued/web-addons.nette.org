<?php

namespace NetteAddons\Model;


class PackageRouteHelper extends \Nette\Object
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
	 * @return int|NULL
	 */
	public function filterIn($composerFullName)
	{
		$row = $this->addons->findOneByComposerFullName($composerFullName);
		if (!$row) {
			return NULL;
		}
		return $row->id;
	}


	/**
	 * @param int
	 * @return string|NULL
	 */
	public function filterOut($id)
	{
		$row = $this->addons->find($id);
		if (!$row) {
			return NULL;
		}
		return $row->composerVendor . '/' . $row->composerName;
	}
}
