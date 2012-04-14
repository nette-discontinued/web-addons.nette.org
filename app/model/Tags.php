<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;



/**
 * Tags repository
 */
class Tags extends Table
{

	const LEVEL_CATEGORY = 1;
	const LEVEL_SUBCATEGORY = 2;
	const LEVEL_ORDINARY_TAG = 9;

	/**
	 * @var string
	 */
	protected $tableName = 'tag';


	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function findMainTags()
	{
		return $this->findAll()->where('level = ?', self::LEVEL_CATEGORY);
	}


	/**
	 * @param \Nette\Database\Table\ActiveRow $addon
	 * @param string|\Nette\Database\Table\ActiveRow $tag
	 */
	public function addAddonTag(ActiveRow $addon, $tag)
	{
		try {
			if (!$tag instanceof ActiveRow) {
				$tag = $this->getTable()
					->where('name = ? OR slug = ? OR id = ?', $tag, $tag, $tag)
					->limit(1)->fetch();
			}

			$this->getAddonTags()->insert(array(
				'addonId' => $addon->getPrimary(),
				$tag->getPrimary()
			));

			return TRUE;

		} catch (\PDOException $e) {
			return FALSE;
		}
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getAddonTags()
	{
		return $this->database->table('addon_tag');
	}

}
