<?php

namespace NetteAddons\Model;

use Nette\Database\SqlLiteral;
use Nette\Database\Context;
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


	public function __construct(Context $db, VersionDependencies $dependencies, Utils\VersionParser $parser)
	{
		parent::__construct($db);

		$this->dependencies = $dependencies;
		$this->parser = $parser;
	}



	/**
	 * Adds new version of given addon.
	 *
	 * @param AddonVersion
	 * @return \Nette\Database\Table\ActiveRow created row
	 * @throws \NetteAddons\DuplicateEntryException
	 * @throws \PDOException
	 * @throws \Nette\Utils\JsonException
	 */
	public function add(AddonVersion $version)
	{
		$row = $this->createRow($this->toArray($version));
		$version->id = $row->id;
		$this->dependencies->setVersionDependencies($version);
		return $row;
	}


	/**
	 * Updates addon version.
	 *
	 * @param AddonVersion
	 * @return bool
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function update(AddonVersion $version)
	{
		if (!$version->id) {
			throw new \NetteAddons\InvalidArgumentException();
		}

		$row = $this->find($version->id);

		if (!$row) {
			return FALSE;
		}

		$res = $row->update($this->toArray($version));

		$this->db->table('addons_dependencies')->where('versionId = ?', $version->id)->delete();

		$this->dependencies->setVersionDependencies($version);

		return (bool) $res;
	}


	/**
	 * Sorts list of versions.
	 *
	 * @param AddonVersion[]
	 * @return void
	 */
	public function rsort(&$versions)
	{
		$this->parser->sort($versions, TRUE);
	}


	/**
	 * Returns the latest version.
	 *
	 * @param AddonVersion[] reverse sorted versions
	 * @param bool
	 * @return AddonVersion|FALSE
	 */
	public function getCurrent($versions, $preferStable = TRUE)
	{
		if (count($versions) === 0) {
			return FALSE;
		}

		if ($preferStable) {
			$stable = $this->parser->filterStable($versions);

			if ($stable) {
				$versions = $stable;
			}
		}

		krsort($versions);

		return reset($versions);
	}


	/**
	 * @param AddonVersion
	 * @return array
	 */
	private function toArray(AddonVersion $version)
	{
		return array(
			'addonId' => $version->addon->id,
			'version' => $version->version,
			'license' => $version->license,
			'distType' => $version->distType,
			'distUrl' => $version->distUrl,
			'sourceType' => $version->sourceType,
			'sourceUrl' => $version->sourceUrl,
			'sourceReference' => $version->sourceReference,
			'composerJson' => Json::encode($version->composerJson),
			'updatedAt' => $version->updatedAt,
		);
	}
}
