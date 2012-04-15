<?php

namespace NetteAddons\Model;

use Nette;
use Nette\Utils\Strings;


/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Addon extends Nette\Object
{

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $composerName;

	/**
	 * @var int
	 */
	public $userId;

	/**
	 * @var string
	 */
	public $shortDescription;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $repository;

	/**
	 * @var string URL to addon demo.
	 */
	public $demo;

	/**
	 * @var array|AddonVersion[]
	 */
	public $versions = array();

	/**
	 * @var array|string[]
	 */
	public $tags = array();



	/**
	 * @param \Nette\Database\Table\ActiveRow|\stdClass $row
	 * @return Addon
	 */
	public static function fromActiveRow(Nette\Database\Table\ActiveRow $row)
	{
		$addon = new static;
		$addon->name = $row->name;
		$addon->composerName = $row->composerName;
		$addon->shortDescription = $row->shortDescription;
		$addon->description = $row->description;
		$addon->demo = $row->demo;
		$addon->repository = $row->repository;
		$addon->userId = (int)$row->user->id;

		foreach ($row->related('addons_tags') as $addonTag) {
			$addon->tags[] = $addonTag->tag->name;
		}

		/** @var \Nette\Database\Table\ActiveRow|\stdClass $versionRow */
		foreach ($row->related('addons_versions') as $versionRow) {
			$version = new AddonVersion();
			$version->version = $versionRow->version;
			$version->license = $versionRow->license;

			/** @var \Nette\Database\Table\ActiveRow|\stdClass $dependencyRow */
			foreach ($versionRow->related('addons_dependencies') as $dependencyRow) {
				$type = $dependencyRow->type;

				if (isset($dependencyRow->dependencyId)) {
					$dependency = $dependencyRow->ref('addons_versions')->via('dependencyId');
					$dependencyAddon = $dependencyRow->ref($dependency->ref('addons'));
					$dependencyName = $dependencyAddon->composerName;
					$version->{$type}[$dependencyName] = $dependencyRow->version;
				} else {
					$version->{$type}[$dependencyRow->packageName] = $dependencyRow->version;
				}
			}

			$addon->versions[$version->version] = $version;
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
