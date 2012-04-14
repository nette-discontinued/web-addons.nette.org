<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Strings;



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
		if (!$tag instanceof ActiveRow) {
			$tag = $this->getTable()
				->where('name = ? OR slug = ? OR id = ?', $tag, $tag, $tag)
				->limit(1)->fetch();

			if (!$tag) {
				$tag = $this->createOrUpdate(array(
					'name' => func_get_arg(1),
					'slug' => Strings::webalize(func_get_arg(1))
				));
			}
		}

		$this->getAddonTags()->insert(array(
			'addon_id' => $addon->id,
			'tag_id' => $tag->id
		));

		return TRUE;
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getAddonTags()
	{
		return $this->connection->table('addon_tag');
	}

	public function isCategory(ActiveRow $tag)
	{
		return $tag->level == Static::LEVEL_CATEGORY;
	}

	public function isSubCategory(ActiveRow $tag)
	{
		return $tag->level == Static::LEVEL_SUBCATEGORY;
	}

}

