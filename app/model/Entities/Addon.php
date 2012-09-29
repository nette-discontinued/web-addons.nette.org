<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Utils\Strings;
use Nette\DateTime;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Addon extends Nette\Object
{
	/** @var int */
	public $id;

	/** @var string */
	public $name;

	/** @var string */
	public $composerName;

	/** @var int */
	public $userId;

	/** @var string single line description */
	public $shortDescription;

	/** @var string */
	public $description;

	/** @var string */
	public $descriptionFormat = 'texy';

	/** @var string default license for new versions */
	public $defaultLicense;

	/** @var string|NULL repository URL	 */
	public $repository;

	/** @var string|NULL */
	public $repositoryHosting;

	/** @var string|NULL URL to addon demo. */
	public $demo;

	/** @var DateTime */
	public $updatedAt;

	/** @var AddonVersion[] */
	public $versions = array();

	/** @var string[] */
	public $tags = array();

	/** @var int total times this addon was downloaded */
	public $totalDownloadsCount = 0;

	/** @var int total times this addon was installed using composer */
	public $totalInstallsCount = 0;


	/**
	 * Creates Addon entity from Nette\Database row.
	 *
	 * @todo   Consider lazy loading for versions and tags.
	 *
	 * @param  Nette\Database\Table\ActiveRow
	 * @return Addon
	 */
	public static function fromActiveRow(Nette\Database\Table\ActiveRow $row)
	{
		$addon = new static;
		$addon->id = (int) $row->id;
		$addon->name = $row->name;
		$addon->composerName = $row->composerName;
		$addon->userId = (int) $row->user->id;
		$addon->shortDescription = $row->shortDescription;
		$addon->description = $row->description;
		$addon->descriptionFormat = $row->descriptionFormat;
		$addon->defaultLicense = $row->defaultLicense;
		$addon->repository = $row->repository;
		$addon->repositoryHosting = $row->repositoryHosting;
		$addon->demo = $row->demo;
		$addon->updatedAt = ($row->updatedAt ? DateTime::from($row->updatedAt) : NULL);
		$addon->totalDownloadsCount = $row->totalDownloadsCount ?: 0;
		$addon->totalInstallsCount = $row->totalInstallsCount ?: 0;

		foreach ($row->related('versions') as $versionRow) {
			$version = AddonVersion::fromActiveRow($versionRow);
			$version->addon = $addon;
			$addon->versions[$version->version] = $version;
		}

		foreach ($row->related('tags') as $tagRow) {
			$addon->tags[] = $tagRow->tag->name;
		}

		return $addon;
	}



	/**
	 * Sets the composer name.
	 * It is built from the current package name and specified username.
	 *
	 * Requires owner in form of Identity or ActiveRow that has name.
	 *
	 * @param \Nette\Security\Identity|\Nette\Database\Table\ActiveRow $owner
	 * @throws \Nette\InvalidArgumentException
	 * @return void
	 */
	public function updateComposerName($owner)
	{
		if (!isset($owner->name)) {
			throw new Nette\InvalidArgumentException("Owner has no name!");
		}

		$this->composerName = $this->sanitizeName($owner->name) . '/' . $this->sanitizeName($this->name);
	}



	/**
	 * @param $string
	 * @return mixed
	 */
	private function sanitizeName($string)
	{
		$name = Strings::toAscii($string);
		return preg_replace('#[^A-Za-z0-9]#i', '', $name);
	}
}
