<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;



/**
 * Version dependencies repository
 */
class VersionDependencies extends Table
{

	/**
	 * @var string
	 */
	protected $tableName = 'addons_dependencies';



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 * @param \NetteAddons\Model\AddonVersion $version
	 */
	public function setVersionDependencies(ActiveRow $addon, AddonVersion $version)
	{
		foreach (array('require', 'suggest', 'provide', 'replace', 'conflict', 'recommend') as $type) {
			foreach ($version->$type as $packageName => $versionName) {
				if (strpos($packageName, '/') !== FALSE){
					if ($dep = $this->findAddon($packageName)) {
						$insert = array(
							'dependencyId' => $dep->getPrimary()
						);
					}
				}

				if (!isset($insert)) {
					$insert = array(
						'packageName' => $packageName
					);
				}

				$this->createOrUpdate(array(
					'addonId' => $addon->getPrimary(),
					'version' => $versionName,
					'type' => $type,
				) + $insert);
			}
		}
	}



	/**
	 * @param string $vendorName
	 * @param string $packageName
	 * @return \Nette\Database\Table\ActiveRow
	 */
	private function findAddon($composerName)
	{
		return $this->connection->table('addon')
			->where('composerName = ?', $composerName)
			->limit(1)->fetch();
	}

}
