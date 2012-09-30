<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Jan Tvrdík
 */
class Tag extends Nette\Object
{
	/** @var int */
	public $id;

	/** @var string */
	public $name;

	/** @var string */
	public $slug;

	/** @var int */
	public $level;



	/**
	 * @param  Nette\Database\Table\ActiveRow
	 * @return Tag
	 */
	public static function fromActiveRow(Nette\Database\Table\ActiveRow $row)
	{
		$tag = new static;
		$tag->id = (int) $row->id;
		$tag->name = $row->name;
		$tag->slug = $row->slug;
		$tag->level = (int) $row->level;

		return $tag;
	}
}
