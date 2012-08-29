<?php

namespace NetteAddons\Model\Utils;

use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonVersion;
use Nette\Utils\Json;
use stdClass;



/**
 * Generating JSON for Composer API
 *
 * @author Jan Marek
 * @author Jan Tvrdík
 */
class Composer
{
	/** composer file name */
	const FILENAME = 'composer.json';



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \NetteAddons\StaticClassException();
	}



	/**
	 * Generates composer.json data
	 *
	 * @todo   Implement setting authors.
	 * @param  AddonVersion
	 * @param  stdClass|NULL original composer.json
	 * @return stdClass
	 */
	public static function createComposerJson(AddonVersion $version, stdClass $orig = NULL)
	{
		if ($version->addon === NULL) {
			throw new \NetteAddons\InvalidArgumentException('$version must hold reference to addon.');
		}

		$composer = $orig ? (clone $orig) : new stdClass();

		if (empty($composer->name)) {
			$composer->name = $version->addon->composerName;
		}

		if (empty($composer->description)) {
			$composer->description = $version->addon->shortDescription;
		}

		$composer->version = $version->version;
		$composer->license = array_map('trim', explode(',', $version->license));

		if (empty($composer->authors)) {
			throw new \NetteAddons\NotImplementedException();
			/*$composer->authors = array(
				'name' => $addon->author->name,
			);*/
		}

		$composer->dist = (object) array(
			'type' => $version->distType,
			'url' => $version->distUrl,
			'reference' => NULL, // or use $version->sourceReference?
			'shasum' => NULL,
		);

		if ($version->sourceUrl) {
			$composer->source = (object) array(
				'type' => $version->sourceType,
				'url' => $version->sourceUrl,
				'reference' => $version->sourceReference,
			);
		}

		return $composer;
	}



	/**
	 * Generates packages.json.
	 *
	 * @param  Addon[]
	 * @return stdClass
	 */
	public static function createPackagesJson(array $addons)
	{
		$file = new stdClass();
		$file->packages = $packages = new stdClass();

		foreach ($addons as $addon) {
			$packages->{$addon->composerName} = new stdClass();
			foreach ($addon->versions as $version) {
				$packages->{$addon->composerName}->{$version->version} = $version->composerJson;
			}
		}

		return $file;
	}
}
