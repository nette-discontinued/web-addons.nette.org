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
	protected $tableName = 'addon_dependency';



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
							'dependency_id' => $dep->getPrimary()
						);
					}
				}

				if (!isset($insert)) {
					$insert = array(
						'package_name' => $packageName
					);
				}

				$this->createOrUpdate(array(
					'addon_id' => $addon->getPrimary(),
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
			->where('composer_name = ?', $composerName)
			->limit(1)->fetch();
	}

}
