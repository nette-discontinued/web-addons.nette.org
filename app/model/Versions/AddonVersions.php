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
	protected $tableName = 'addons_versions';



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
			'addonId' => $addon->getPrimary(),
			'version' => $version->version,
			'license' => $version->license,
			'filename' => $version->filename
		));
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 */
	public function findAddonCurrentVersion(ActiveRow $addon)
	{
		$versions = $addon->related('addons_versions')->fetchPairs('id', 'version');
		$hasMasterBranch = in_array('master', $versions);
		$versions = array_filter($versions, function ($ver) {
			return (bool)Version::create($ver);
		});

		if (!$versions && $hasMasterBranch) {
			return "master";
		}

		usort($versions, function ($me, $him) {
			$me = Version::create('>=' . $me);
			return $me->match($him) ? 1 : -1;
		});

		return end($versions);
	}

}
