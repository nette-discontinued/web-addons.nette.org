<?php

namespace NetteAddons\Model;

use Nette;



/**
 */
interface IAddonImporter
{

	/**
	 * Returns informations about Addon.
	 *
	 * @return Addon
	 */
	function import();

}
