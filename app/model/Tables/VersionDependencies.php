<?php

namespace NetteAddons\Model;

use Nette\Utils\Strings;


class VersionDependencies extends Table
{
	/** @var string */
	protected $tableName = 'addons_dependencies';


	/**
	 * @param AddonVersion
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
	 * @param string
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	private function findAddon($composerFullName)
	{
		$composerVendor = $composerName = NULL;
		if (($data = Strings::match($composerFullName, Addon::COMPOSER_NAME_RE)) !== NULL) {
			$composerVendor = $data['vendor'];
			$composerName = $data['name'];
		}
		return $this->db->table('addons')
			->where(array('composerVendor' => $composerVendor, 'composerName' => $composerName))
			->limit(1)->fetch();
	}
}
