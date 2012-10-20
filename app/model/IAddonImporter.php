<?php

namespace NetteAddons\Model;

use Nette;



/**
 *
 */
interface IAddonImporter
{
	/**
	 * @return string
	 */
	public static function getName();



	/**
	 * @param string
	 * @return bool
	 */
	public static function isSupported($url);



	/**
	 * @param string
	 * @return bool
	 */
	public static function isValid($url);


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
