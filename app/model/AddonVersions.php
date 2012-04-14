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
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function setAddonVersion(ActiveRow $addon, AddonVersion $version)
	{
		return $this->createOrUpdate(array(
			'addon_id' => $addon->getPrimary(),
			'version' => $version->version,
		));
	}

}
