<?php

namespace NetteAddons\Model;

use Nette,
	Nette\Utils\Json,
	Nette\Utils\Strings,
	Nette\Database\Table\ActiveRow;


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
	public $requireDev = array();

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

	/** @var int number of installs using composer */
	public $installsCount = 0;

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

	/** @var array */
	public $relatedAddons = array();

	/** @var DateTime|NULL time of version's creation */
	public $updatedAt;



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
		$version->installsCount = $row->installsCount ?: 0;
		$version->sourceType = $row->sourceType;
		$version->sourceUrl = $row->sourceUrl;
		$version->sourceReference = $row->sourceReference;
		$version->composerJson = Json::decode($row->composerJson); // this may fail
		$version->updatedAt = $row->updatedAt;

		$linkTypes = self::getLinkTypes();
		$linkTypes = array_flip($linkTypes);

		foreach ($row->related('dependencies') as $dependencyRow) {
			$type = $dependencyRow->type;
			$type = $linkTypes[$type];
			$version->{$type}[$dependencyRow->packageName] = $dependencyRow->version;
			if ($dependencyRow->dependencyId) {
				$version->relatedAddons[$dependencyRow->packageName] = $dependencyRow->dependencyId;
			}
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
		return array(
			'require' => 'require', 
			'replace' => 'replace', 
			'conflict' => 'conflict', 
			'provide' => 'provide', 
			'requireDev' => 'require-dev',
		);
	}



	/**
	 * If version depends on Nette and Nette version is not specified,
	 * returns FALSE
	 *
	 * @return bool
	 */
	public function hasNetteVersion()
	{
		$version = $this->getNetteVersion();
		return ($version && strpos($version, 'dev') === FALSE);
	}



	/**
	 * Returns version of Nette this addon depends on
	 *
	 * @return string|NULL
	 */
	public function getNetteVersion()
	{
		if (isset($this->require['nette/nette'])) {
			return $this->require['nette/nette'];
		}
	}



	/**
	 * Returns all requirements beside Nette
	 *
	 * @return array
	 */
	public function getOtherRequirements()
	{
		$requirements = $this->require;
		unset($requirements['nette/nette']);
		return $requirements;
	}
}
