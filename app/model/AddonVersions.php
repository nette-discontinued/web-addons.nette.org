<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;



/**
 * Addon versions repository
 */
class AddonVersions extends Table
{

	/**
	 * @var string
	 */
	protected $tableName = 'addon_version';



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 * @param \NetteAddons\Model\AddonVersion $version
	 *
	 * @throws \NetteAddons\InvalidArgumentException
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function setAddonVersion(ActiveRow $addon, AddonVersion $version)
	{
		if (!$version->license) {
			throw new \NetteAddons\InvalidArgumentException("License must be specified");
		}

		return $this->createOrUpdate(array(
			'addon_id' => $addon->getPrimary(),
			'version' => $version->version,
			'license' => $version->license,
			'filename' => $version->filename
		));
	}

}
