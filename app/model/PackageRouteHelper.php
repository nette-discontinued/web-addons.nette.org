<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Pavel Kučera
 */
class PackageRouteHelper extends Nette\Object
{
	/** @var Addons */
	private $addons;



	/**
	 * @param Addons
	 * @param Cache
	 */
	public function __construct(Addons $addons)
	{
		$this->addons = $addons;
	}



	/**
	 * @param string
	 * @return int|NULL
	 */
	public function filterIn($composerName)
	{
		$row = $this->addons->findOneBy(array(
			'composerName' => $composerName,
		));
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
		return $row->composerName;
	}
}
