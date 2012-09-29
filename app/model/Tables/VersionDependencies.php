<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;



/**
 * Version dependencies repository
 */
class VersionDependencies extends Table
{
	/** @var string */
	protected $tableName = 'addons_dependencies';



	/**
	 * @param  AddonVersion
	 * @return void
	 * @throws \NetteAddons\DuplicateEntryException
	 * @throws \PDOException
	 */
	public function setVersionDependencies(AddonVersion $version)
	{
		foreach (AddonVersion::getLinkTypes() as $key => $type) {
			foreach ($version->$key as $packageName => $versionNumber) {
				if (strpos($packageName, '/') !== FALSE && ($dep = $this->findAddon($packageName))) {
					$depId = $dep->getPrimary();
				} else {
					$depId = NULL;
				}

				$this->createRow(array(
					'versionId' => $version->id,
					'dependencyId' => $depId,
					'packageName' => $packageName,
					'version' => $versionNumber,
					'type' => $type,
				));
			}
		}
	}



	/**
	 * Finds addon by composer name.
	 *
	 * @param  string
	 * @return ActiveRow|FALSE
	 */
	private function findAddon($composerName)
	{
		return $this->connection->table('addons')
			->where('composerName = ?', $composerName)
			->limit(1)->fetch();
	}
}
