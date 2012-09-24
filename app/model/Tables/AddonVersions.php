<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Json;



/**
 * Addon versions repository
 */
class AddonVersions extends Table
{

	/** @var string */
	protected $tableName = 'addons_versions';

	/** @var VersionDependencies version dependencies repository */
	private $dependencies;

	/** @var Utils\VersionParser */
	private $parser;



	/**
	 * Class constructor.
	 *
	 * @param  Nette\Database\Connection
	 * @param  Utils\VersionParser
	 */
	public function __construct(Nette\Database\Connection $dbConn, VersionDependencies $dependencies, Utils\VersionParser $parser)
	{
		parent::__construct($dbConn);
		$this->dependencies = $dependencies;
		$this->parser = $parser;
	}



	/**
	 * Adds new version of given addon.
	 *
	 * @param  AddonVersion
	 * @return ActiveRow created row
	 * @throws \NetteAddons\DuplicateEntryException
	 * @throws \PDOException
	 * @throws \Nette\Utils\JsonException
	 */
	public function add(AddonVersion $version)
	{
		$row = $this->createRow(array(
			'addonId'         => $version->addon->id,
			'version'         => $version->version,
			'license'         => $version->license,
			'distType'        => $version->distType,
			'distUrl'         => $version->distUrl,
			'sourceType'      => $version->sourceType,
			'sourceUrl'       => $version->sourceUrl,
			'sourceReference' => $version->sourceReference,
			'composerJson'    => Json::encode($version->composerJson),
		));

		$version->id = $row->id;
		$this->dependencies->setVersionDependencies($version);
		return $row;
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 */
	public function findAddonCurrentVersion(ActiveRow $addon)
	{
		$versions = $addon->related('addons_versions')->fetchPairs('id', 'version');
		$hasMasterBranch = in_array('dev-master', $versions);
		$versions = array_filter($versions, function ($ver) {
			return (bool)Version::create($ver);
		});

		if (!$versions && $hasMasterBranch) {
			return 'dev-master';
		}

		usort($versions, function ($me, $him) {
			$me = Version::create('>=' . $me);
			return $me->match($him) ? 1 : -1;
		});

		return end($versions);
	}



	/**
	 * Sorts list of versions.
	 *
	 * @param  AddonVersion[]
	 * @return void
	 */
	public function rsort(&$versions)
	{
		$this->parser->sort($versions, TRUE);
	}
}
