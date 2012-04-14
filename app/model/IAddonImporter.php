<?php

namespace NetteAddons\Model;

use Nette;



/**
 *
 */
interface IAddonImporter
{
	/**
	 * @return Addon
	 */
	public function import();

	/**
	 * @return AddonVersion[]
	 */
	public function importVersions();
}
