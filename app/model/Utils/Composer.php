<?php

namespace NetteAddons\Model\Utils;

use stdClass;
use JsonSchema;
use Nette\Utils\Json;
use NetteAddons\Model\AddonVersion;


/**
 * Generating JSON for Composer API
 */
class Composer
{
	/** composer file name */
	const FILENAME = 'composer.json';


	/**
	 * @throws \NetteAddons\StaticClassException
	 */
	final public function __construct()
	{
		throw new \NetteAddons\StaticClassException;
	}


	/**
	 * Validates composer.json structure using JSON Schema.
	 *
	 * @link https://github.com/composer/composer/blob/master/res/composer-schema.json
	 * @link https://github.com/justinrainbow/json-schema/
	 * @link http://json-schema.org/
	 * @author Jan Tvrdík
	 * @param  stdClass|mixed
	 * @return bool
	 * @throws \NetteAddons\InvalidStateException
	 */
	public static function isValid($composer)
	{
		if (!$composer instanceof stdClass) {
			return FALSE;
		}

		try {
			$schema = file_get_contents(__DIR__ . '/composer-schema.json');
			$schema = Json::decode($schema);
		} catch (\Nette\Utils\JsonException $e) {
			throw new \NetteAddons\InvalidStateException('composer-schema.json is not valid JSON file.', NULL, $e);
		}

		$validator = new JsonSchema\Validator();
		$validator->check($composer, $schema);

		return $validator->isValid();
	}



	/**
	 * Generates composer.json data
	 *
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
			$composer->name = $version->addon->composerFullName;
		}

		if (empty($composer->description)) {
			$composer->description = $version->addon->shortDescription;
		}

		$composer->version = $version->version;
		$composer->license = array_map('trim', explode(',', $version->license));

		if (!self::isValid($composer)) {
			throw new \NetteAddons\InvalidStateException();
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
	 * @param Addon[]|array
	 * @return stdClass
	 */
	public static function createPackagesJson(array $addons)
	{
		$file = new stdClass();
		$file->packages = $packages = new stdClass();

		foreach ($addons as $addon) {
			$packages->{$addon->composerFullName} = new stdClass();
			foreach ($addon->versions as $version) {
				$packages->{$version->composerJson->name}->{$version->version} = $version->composerJson;
			}
		}

		return $file;
	}
}
