<?php

namespace NetteAddons\Model;

use Nette;



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
	public $vendorName;

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
		$addon->vendorName = $row->vendor_name;
		$addon->description = $row->short_description;

		foreach ($row->related('addon_tag') as $addonTag) {
			$addon->tags[] = $addonTag->tag->name;
		}

		foreach ($row->related('addon_version') as $versionRow) {
			$version = new AddonVersion();
			$version->version = $versionRow->version;

			foreach ($versionRow->related('addon_dependency') as $dependencyRow) {
				$type = $dependencyRow->type;

				if (isset($dependencyRow->dependency_id)) {
					$dependency = $dependencyRow->ref('addon_version')->via('dependency_id');
					$dependencyAddon = $dependencyRow->ref($dependency->ref('addon'));
					$dependencyName = $dependencyAddon->vendor_name . '/' . $dependencyAddon->name;
					$version->{$type}[$dependencyName] = $dependencyRow->version;
				} else {
					$version->{$type}[$dependencyRow->package_name] = $dependencyRow->version;
				}
			}

			$addon->versions[$version->version] = $version;
		}

		return $addon;
	}

}
