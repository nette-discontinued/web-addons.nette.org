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
	protected $tableName = 'addons';



	/**
	 * @param \NetteAddons\Model\IAddonImporter $importer
	 */
	public function import(IAddonImporter $importer)
	{
		$addon = $importer->import();
		throw new Nette\NotImplementedException;
	}

}
