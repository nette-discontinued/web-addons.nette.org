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
	 * @param  Addon
	 * @return AddonVersion[]
	 */
	public function importVersions(Addon $addon);
}
