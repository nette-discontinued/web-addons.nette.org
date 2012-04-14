<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;



/**
 */
class Addons extends Table
{

	/**
	 * @var string
	 */
	protected $tableName = 'addon';



	/**
	 * @param int $id
	 * @return Addon|boolean
	 */
	public function get($id)
	{
		if (!$row = $this->getTable()->get($id)) {
			return FALSE;
		}

		$addon = new Addon();
		$addon->name = $row->name;
		foreach ($this->findAddonVersions($row) as $versionRow) {
			$addon->versions[] = $version = new AddonVersion();
			$version->version = $versionRow->version;

			foreach ($this->findVersionDependencies($versionRow) as $dependency) {
				if ($dependency->package_name) {
					$version->{$dependency->type}[$dependency->package_name] = $dependency->package_version;

				} else {
					$version->{$dependency->type}[$dependency->dependency->name] = $dependency->package_version;
				}
			}
		}

		foreach ($this->findAddonTags($row) as $tagRow) {
			$addon->tags[] = $tagRow->tag->name;
		}

		return $addon;
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addonVersion
	 * @return \Nette\Database\Table\GroupedSelection
	 */
	public function findVersionDependencies(ActiveRow $addonVersion)
	{
		return $addonVersion->related('addon_dependency');
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 *
	 * @return \Nette\Database\Table\GroupedSelection
	 */
	public function findAddonTags(ActiveRow $addon)
	{
		return $addon->related('addon_tag');
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 *
	 * @return \Nette\Database\Table\GroupedSelection
	 */
	public function findAddonVersions(ActiveRow $addon)
	{
		return $addon->related('addon_version');
	}

}
