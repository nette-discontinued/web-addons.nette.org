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
	 * @param  Addon
	 * @param  AddonVersion
	 * @return \Nette\Database\Table\ActiveRow created row
	 */
	public function add(Addon $addon, AddonVersion $version)
	{
		/*if ($version->uplodedFile) {
			if (!$version->uplodedFile->isOk()) {
				throw new \NetteAddons\InvalidArgumentException('Uploaded file is not OK.');
			}

			$fileName = $version->getFilename($addon);
			$version->uplodedFile->move($this->uploadDir . '/' . $fileName);
			$version->link = $this->uploadBaseUrl . '/' . $fileName;

		} elseif (empty($version->link)) {
			throw new \NetteAddons\InvalidArgumentException('AddonVersion::$link is required.');
		}*/

		// $this->connection->query('SAVEPOINT addVersion');

		try {
			$row = $this->createRow(array(
				'addonId'      => $addon->id,
				'version'      => $version->version,
				'license'      => $version->license /*?: $addon->defaultLicense*/,
				'link'         => $version->link,
				'composerJson' => $version->composerJson,
			));

			$this->dependencies->setVersionDependencies($addon, $version);
			// $this->connection->query('');
			return $row;

		} catch (\Exception $e) {
			// $this->connection->rollBack();
			throw $e;
		}
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
