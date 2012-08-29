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

	/** @var string type of distribution archive ('zip' or 'tarball') */
	public $distType;

	/** @var string URL of distribution archive */
	public $distUrl;

	/** @var string|NULL VCS type ('git', 'hg' or 'svn') */
	public $sourceType;

	/** @var string|NULL repository URL */
	public $sourceUrl;

	/** @var string|NULL Git, Mercurial or SVN reference (usually branch or tag name) */
	public $sourceReference;

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
