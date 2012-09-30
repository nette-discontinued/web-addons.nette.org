<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection as TableSelection;
use Nette\Utils\Strings;



/**
 * Tags repository
 */
class Tags extends Table
{
	const LEVEL_CATEGORY = 1;
	const LEVEL_SUBCATEGORY = 2;
	const LEVEL_ORDINARY_TAG = 9;

	/** @var string */
	protected $tableName = 'tags';



	/**
	 * Returns tags which represent main catagories.
	 *
	 * @return TableSelection
	 */
	public function findMainTags()
	{
		return $this->findAll()->where('level = ?', self::LEVEL_CATEGORY);
	}



	/**
	 * @param  ActiveRow
	 * @param  string|ActiveRow
	 * @return bool
	 */
	public function addAddonTag(ActiveRow $addon, $tag)
	{
		if (!$tag instanceof ActiveRow) {
			$tag = $this->getTable()
				->where('name = ? OR slug = ? OR id = ?', $tag, $tag, $tag) // JT: I don't like this.
				->limit(1)->fetch();

			if (!$tag) {
				$tag = $this->createOrUpdate(array(
					'name' => func_get_arg(1),
					'slug' => Strings::webalize(func_get_arg(1)),
					'level' => self::LEVEL_ORDINARY_TAG,
					'visible' => TRUE,
				));
			}
		}

		try {
			$this->getAddonTags()->insert(array(
				'addonId' => $addon->id,
				'tagId' => $tag->id
			));
		} catch (\PDOException $e) {
			// duplicate entry is not an error in this case
			// TODO: Rethrow the exception if inserting fails for different reason.
		}

		return TRUE;
	}



	/**
	 * @return TableSelection
	 */
	protected function getAddonTags()
	{
		return $this->connection->table('addons_tags');
	}



	/**
	 * Checks whether given tag represents main category.
	 *
	 * @param  ActiveRow
	 * @return bool
	 */
	public function isCategory(ActiveRow $tag)
	{
		return $tag->level == Static::LEVEL_CATEGORY;
	}



	/**
	 * Checks whether given tag represents subcategory.
	 *
	 * @param  ActiveRow
	 * @return bool
	 */
	public function isSubCategory(ActiveRow $tag)
	{
		return $tag->level == Static::LEVEL_SUBCATEGORY;
	}



	/**
	 * Returns parent category for given category.
	 *
	 * @param  ActiveRow
	 * @return ActiveRow|NULL
	 */
	public function getParentCategory(ActiveRow $tag)
	{
		if (!$this->isSubCategory($tag)) {
			return NULL;
		}

		return $this->getTable()
			->find($tag->parent_id)
			->fetch();
	}



	/**
	 * Returns subcategories of given category
	 *
	 * @param \Nette\Database\Table\ActiveRow $tag
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function getSubCategories(ActiveRow $tag)
	{
		if (!$this->isCategory($tag)) {
			return null;
		}

		return $this->getTable()
			->where('parent_id', $tag->id);
	}
}
