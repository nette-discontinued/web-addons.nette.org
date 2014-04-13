<?php

namespace NetteAddons\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\Strings;


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
	 * @param bool
	 * @return \Nette\Database\Table\Selection
	 */
	public function findMainTags($ignoreHidden = FALSE)
	{
		$selection = $this->findAll()->where('level = ?', self::LEVEL_CATEGORY);
		if (!$ignoreHidden) {
			$selection->where('visible = ?', true);
		}
		return $selection;
	}


	/**
	 * Returns tags which represent main catagories (only with at least one addon).
	 *
	 * @param bool
	 * @return \Nette\Database\Table\Selection
	 */
	public function findMainTagsWithAddons($ignoreHidden = FALSE)
	{
		return $this->findMainTags($ignoreHidden)->group('tags.id')->having('COUNT(:addons_tags.tagId) > 0');
	}


	/**
	 * @param string
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function findOneBySlug($slug)
	{
		return $this->findOneBy(array('slug' => $slug));
	}


	public function saveAddonTags(Addon $addon)
	{
		if (count($addon->tags) === 0) return;
		$tags = array();
		foreach ($addon->tags as $tag) {
			if ($tag instanceof Tag) {
				$tags[] = $tag->id;
			} elseif (is_string($tag) && !ctype_digit($tag)) {
				$tags[] = $this->ensureExistence($tag);
			} elseif (is_int($tag) || ctype_digit($tag)) {
				$tags[] = (int) $tag;
			}
		}

		$current = array_keys($this->getAddonTags()->where('addonId', $addon->id)->fetchPairs('tagId'));
		$tags2Remove = array_values(array_diff($current, $tags));
		$newTags = array_diff($tags, $current);

		$this->getAddonTags()->where(array(
			'addonId' => $addon->id,
			'tagId' => $tags2Remove,
		))->delete();

		foreach ($newTags as $tagId) {
			$this->getAddonTags()->insert(array(
				'addonId' => $addon->id,
				'tagId' => $tagId,
			));
		}
	}


	/**
	 * @param string tag name
	 * @return int tag id
	 */
	public function ensureExistence($tagName)
	{
		try {
			$slug = Strings::webalize($tagName);
			$row = $this->createRow(array(
				'name' => $tagName,
				'slug' => $slug,
				'level' => self::LEVEL_ORDINARY_TAG,
				'visible' => TRUE,
			));
		} catch (\NetteAddons\DuplicateEntryException $e) {
			$row = $this->findOneBy(array(
				'slug' => $slug,
			));
		}

		return (int) $row->id;
	}


	/**
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getAddonTags()
	{
		return $this->db->table('addons_tags');
	}


	/**
	 * Checks whether given tag represents main category.
	 *
	 * @param  \Nette\Database\Table\ActiveRow
	 * @return bool
	 */
	public function isCategory(ActiveRow $tag)
	{
		return $tag->level == Static::LEVEL_CATEGORY;
	}


	/**
	 * Checks whether given tag represents subcategory.
	 *
	 * @param  \Nette\Database\Table\ActiveRow
	 * @return bool
	 */
	public function isSubCategory(ActiveRow $tag)
	{
		return $tag->level == Static::LEVEL_SUBCATEGORY;
	}


	/**
	 * Returns parent category for given category.
	 *
	 * @param  \Nette\Database\Table\ActiveRow
	 * @return \Nette\Database\Table\ActiveRow|NULL
	 */
	public function getParentCategory(ActiveRow $tag)
	{
		if (!$this->isSubCategory($tag)) {
			return NULL;
		}

		return $this->getTable()
			->wherePrimary($tag->parent_id)
			->fetch();
	}


	/**
	 * Returns subcategories of given category.
	 *
	 * @param  \Nette\Database\Table\ActiveRow
	 * @return \Nette\Database\Table\Selection
	 */
	public function getSubCategories(ActiveRow $tag)
	{
		return $this->getTable()
			->where('parent_id', $tag->id);
	}
}
