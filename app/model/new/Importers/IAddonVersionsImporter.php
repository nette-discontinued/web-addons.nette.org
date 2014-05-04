<?php

namespace NetteAddons\Model\Importers;

interface IAddonVersionsImporter
{
	/**
	 * @param string
	 * @return \NetteAddons\Model\AddonEntity
	 */
	public function getAddon($url);

	/**
	 * @param string
	 * @return boolean
	 */
	public function isSupported($url);
}
