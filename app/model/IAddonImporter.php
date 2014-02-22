<?php

namespace NetteAddons\Model;


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
	 * @return \NetteAddons\Model\Addon
	 */
	public function import();


	/**
	 * @param \NetteAddons\Model\Addon
	 * @return \NetteAddons\Model\AddonVersion[]
	 */
	public function importVersions(Addon $addon);
}
