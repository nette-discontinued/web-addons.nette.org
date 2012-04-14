<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;



/**
 * Addons table repository
 */
class Addons extends Table
{

	/**
	 * @var string
	 */
	protected $tableName = 'addon';



	/**
	 * @param \Nette\Database\Table\Selection $addons
	 * @param string $tag
	 * @return \Nette\Database\Table\Selection
	 */
	public function filterByTag(Selection $addons, $tag)
	{
		$addonIds = $this->connection->table('addon_tag')
			->where('tag_id = ?', $tag)->select('addon_id');

		$addons->where('id', $addonIds);

		return $addons;
	}



	public function filterByString(Selection $addons, $string)
	{
		$string = "%$string%";
		$addons->where('name LIKE ? OR short_description LIKE ?', $string, $string);
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
