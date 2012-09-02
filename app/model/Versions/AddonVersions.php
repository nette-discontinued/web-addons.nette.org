<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;



/**
 * Addon versions repository
 */
class AddonVersions extends Table
{

	/** @var string */
	protected $tableName = 'addons_versions';

	/** @var VersionDependencies version dependencies repository */
	private $dependencies;



	/**
	 * Class constructor.
	 *
	 * @param  Nette\Database\Connection
	 */
	public function __construct(Nette\Database\Connection $dbConn, VersionDependencies $dependencies)
	{
		parent::__construct($dbConn);
		$this->dependencies = $dependencies;
	}



	/**
	 * Adds new version of given addon.
	 *
	 * @param  AddonVersion
	 * @return ActiveRow created row
	 * @throws \NetteAddons\DuplicateEntryException
	 * @throws \PDOException
	 */
	public function add(AddonVersion $version)
	{
		$row = $this->createRow(array(
			'addonId' => $version->addon->id,
			'version' => $version->version,
			'license' => $version->license,
			'distType' => $version->distType,
			'distUrl' => $version->distUrl,
			'sourceType' => $version->sourceType,
			'sourceUrl' => $version->sourceUrl,
			'sourceReference' => $version->sourceReference,
			'composerJson' => $version->composerJson,
		));

		$this->dependencies->setVersionDependencies($version);
		return $row;
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
