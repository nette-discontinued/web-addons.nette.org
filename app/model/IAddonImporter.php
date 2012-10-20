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
	 * @param string
	 * @return string
	 */
	public static function normalizeUrl($url);


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
