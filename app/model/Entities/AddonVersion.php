<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Nette\Database\Table\ActiveRow;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AddonVersion extends Nette\Object
{
	/** @var int */
	public $id;

	/** @var string */
	public $version;

	/** @var string */
	public $license;

	/** @var string[] */
	public $require = array();

	/** @var string[] */
	//public $suggest = array();

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

	/** @var int number of downloads */
	public $downloadsCount = 0;

	/** @var string|NULL VCS type ('git', 'hg' or 'svn') */
	public $sourceType;

	/** @var string|NULL repository URL */
	public $sourceUrl;

	/** @var string|NULL Git, Mercurial or SVN reference (usually branch or tag name) */
	public $sourceReference;

	/** @var \stdClass composer.json with "source" and/or "dist" fields */
	public $composerJson;

	/** @var Addon|NULL */
	public $addon;



	/**
	 * Creates AddonVersion entity from Nette\Database row.
	 *
	 * @author Filip Procházka
	 * @author Jan Tvrdík
	 * @param  ActiveRow
	 * @return AddonVersion
	 * @throws \Nette\Utils\JsonException if $row->composerJson contains invalid JSON
	 */
	public static function fromActiveRow(ActiveRow $row)
	{
		$version = new static;
		$version->id = $row->id;
		$version->version = $row->version;
		$version->license = $row->license;
		$version->distType = $row->distType;
		$version->distUrl = $row->distUrl;
		$version->downloadsCount = $row->downloadsCount ?: 0;
		$version->sourceType = $row->sourceType;
		$version->sourceUrl = $row->sourceUrl;
		$version->sourceReference = $row->sourceReference;
		$version->composerJson = Json::decode($row->composerJson); // this may fail

		foreach ($row->related('dependencies') as $dependencyRow) {
			$type = $dependencyRow->type;
			$version->{$type}[$dependencyRow->packageName] = $dependencyRow->version;
		}

		return $version;
	}



	/**
	 * Returns known types of package links.
	 *
	 * @link http://getcomposer.org/doc/04-schema.md#package-links
	 * @return array
	 */
	public static function getLinkTypes()
	{
		return array('require', 'replace', 'conflict', 'provide', /*'require-dev', 'suggest'*/);
	}
}
