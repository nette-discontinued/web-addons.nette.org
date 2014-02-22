<?php

namespace NetteAddons\Model;

use Nette\Database\Table\ActiveRow;


class Tag extends \Nette\Object
{
	/** @var int */
	public $id;

	/** @var string */
	public $name;

	/** @var string */
	public $slug;

	/** @var int */
	public $level;


	public static function fromActiveRow(ActiveRow $row)
	{
		$tag = new static;
		$tag->id = (int) $row->id;
		$tag->name = $row->name;
		$tag->slug = $row->slug;
		$tag->level = (int) $row->level;

		return $tag;
	}
}
