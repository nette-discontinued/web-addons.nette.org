<?php

namespace NetteAddons\Model;

use Nette;



/**
 */
interface IAddonImporter
{

	/**
	 * Returns array of informations about addon.
	 *
	 * @return Addon
	 */
	function import();

}
