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
	 * @var \Nette\Security\Identity
	 */
	public $user;

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


	public static function fromActiveRow(\Nette\Database\Table\ActiveRow $row)
	{
		$addon = new static;
		$addon->name = $row->name;
		$addon->composerName = $row->composer_name;
		$addon->shortDescription = $row->short_description;
		$addon->description = $row->description;
		$addon->demo = $row->demo;
		$addon->repository = $row->repository;

		foreach ($row->related('addon_tag') as $addonTag) {
			$addon->tags[] = $addonTag->tag->name;
		}

		foreach ($row->related('addon_version') as $versionRow) {
			$version = new AddonVersion();
			$version->version = $versionRow->version;
			$version->license = $versionRow->license;

			foreach ($versionRow->related('addon_dependency') as $dependencyRow) {
				$type = $dependencyRow->type;

				if (isset($dependencyRow->dependency_id)) {
					$dependency = $dependencyRow->ref('addon_version')->via('dependency_id');
					$dependencyAddon = $dependencyRow->ref($dependency->ref('addon'));
					$dependencyName = $dependencyAddon->composer_name;
					$version->{$type}[$dependencyName] = $dependencyRow->version;
				} else {
					$version->{$type}[$dependencyRow->package_name] = $dependencyRow->version;
				}
			}

			$addon->versions[$version->version] = $version;
		}

		return $addon;
	}


	/**
	 * Sets the composer name.
	 * It is built from the current package name and specifed username.
	 */
	public function buildComposerName()
	{
		$this->composerName = $this->trimPackageName($this->user->name) . '/' . $this->trimPackageName($this->name);
	}



	private function trimPackageName($string)
	{
		$name = Strings::toAscii($string);
		return preg_replace('#[^A-Za-z0-9]#i', '', $name);
	}

}
