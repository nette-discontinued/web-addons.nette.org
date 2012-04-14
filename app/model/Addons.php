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
	 * @param \NetteAddons\Model\IAddonImporter $importer
	 */
	public function import(IAddonImporter $importer)
	{
		$addon = $importer->import();
		throw new Nette\NotImplementedException;
	}



	/**
	 * @param \NetteAddons\Model\Addon $addon
	 */
	public function createAddon(Addon $addon)
	{
		throw new Nette\NotImplementedException;
	}



	/**
	 * @param \NetteAddons\Model\Addon $addon
	 */
	public function updateAddon(Addon $addon)
	{
		throw new Nette\NotImplementedException;
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 * @param string|\Nette\Database\Table\ActiveRow $tag
	 */
	public function addAddonTag(ActiveRow $addon, $tag)
	{
		try {
			if (!$tag instanceof ActiveRow){
				$tag = $this->database->table('tags')
					->where('name = ? OR slug = ? OR id = ?', $tag, $tag, $tag)
					->limit(1)->fetch();
			}

			$this->getTagsTable()->insert(array(
				'addonId' => $addon->getPrimary(),
				$tag->getPrimary()
			));

			return TRUE;

		} catch (\PDOException $e) {
			return FALSE;
		}
	}



	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 * @return mixed
	 */
	public function getAddonDependencies(ActiveRow $addon)
	{
		return $addon->related('addon_dependency');
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTagsTable()
	{
		return $this->database->table('addon_tag');
	}

}
