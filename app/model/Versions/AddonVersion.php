<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Utils\Strings;
use Nette\Database\Table\ActiveRow;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AddonVersion extends Nette\Object
{
	/** @var string */
	public $version;

	/** @var string */
	public $license;

	/** @var string URL where this version can be downloaded */
	public $link;

	/** @var string[] */
	public $require = array();

	/** @var string[] */
	public $suggest = array();

	/** @var string[] */
	public $provide = array();

	/** @var string[] */
	public $replace = array();

	/** @var string[] */
	public $conflict = array();

	/** @var string[] */
	public $recommend = array();

	/** @var array */
	public $composerJson = array();



	/**
	 * Creates AddonVersion entity from Nette\Database row.
	 *
	 * @author Filip Procházka
	 * @author Jan Tvrdík
	 * @param  ActiveRow
	 * @return AddonVersion
	 */
	public static function fromActiveRow(ActiveRow $row)
	{
		$version = new static;
		$version->version = $row->version;
		$version->license = $row->license;
		$version->link = $row->link;
		$version->composerJson = $row->composerJson;

		foreach ($row->related('dependencies') as $dependencyRow) {
			$type = $dependencyRow->type;
			$version->{$type}[$dependencyRow->packageName] = $dependencyRow->version;
		}

		return $version;
	}



	/**
	 * Builds the filename.
	 * @param \NetteAddons\Model\Addon $addon
	 * @return string
	 */
	/*public function getFilename(Addon $addon)
	{
		return Strings::webalize($addon->composerName) . '-' . $this->version . '.zip';
	}*/
}
