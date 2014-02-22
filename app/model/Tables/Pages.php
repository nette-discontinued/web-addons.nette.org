<?php

namespace NetteAddons\Model;

use Nette\Utils\Strings;


class Pages extends Table
{
	/** @var string */
	protected $tableName = 'pages';


	/**
	 * @param string
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function findOneBySlug($slug)
	{
		return $this->getTable()->where('slug = ?', $slug)->order('revision DESC')->limit(1)->fetch();
	}


	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function findAll()
	{
		return $this->getTable()->group('slug')->order('revision DESC');
	}


	/**
	 * @param int
	 * @param string
	 * @param string
	 * @return \Nette\Database\Table\ActiveRow
	 * @throws \InvalidArgumentException
	 */
	public function savePage($authorId, $name, $content)
	{
		return $this->createRow(array(
			'authorId' => $authorId,
			'name' => $name,
			'slug' => Strings::webalize($name),
			'content' => $content,
			'createdAt' => new \DateTime,
			'revision' => 1,
		));
	}


	/**
	 * @param int
	 * @param int
	 * @param string
	 * @param string
	 * @param int
	 * @return int|NULL
	 */
	public function updatePage($id, $authorId, $name, $content)
	{
		$row = $this->find($id);

		if (!$row) {
			return NULL;
		}

		return $row->update(array(
			'authorId' => $authorId,
			'name' => $name,
			'content' => $content,
			'createdAt' => new \DateTime,
			'revision' => $row->revision + 1,
		));
	}
}
