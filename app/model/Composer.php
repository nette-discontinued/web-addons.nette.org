<?php

namespace NetteAddons\Model;

use Nette;



/**
 * Generating JSON for Composer API
 *
 * @author Jan Marek
 */
class Composer extends Nette\Object
{
	/** @var Addons addons repository */
	private $addons;



	/**
	 * @param Addons addons repository
	 */
	public function __construct(Addons $addons)
	{
		$this->addons = $addons;
	}



	/**
	 * Generates packages.json data
	 *
	 * @param  Addon[]
	 * @return array
	 */
	public function createPackages(array $addons)
	{
		$packages = array();
		foreach ($addons as $addon) {
			foreach ($addon->versions as $version) {
				$packages[$addon->composerName][$version->version] = $this->createComposerJson($addon, $version);
			}
		}
		return $packages;
	}

	/**
	 * Generates composer.json data
	 *
	 * @param  Addon
	 * @param  AddonVersion
	 * @return array
	 */
	public function createComposerJson(Addon $addon, AddonVersion $version)
	{
		$composer = $version->composerJson;
		$composer['version'] = $version->version;
		$composer['dist'] = array(
			'url' => $this->addons->getZipUrl($addon, $version),
			'type' => 'zip',
		);
		$composer['license'] = array_map('trim', explode(',', $version->license));

		return $composer;
	}

	private function createDefaultComposerJson(Addon $addon, AddonVersion $version)
	{
		throw new \NetteAddons\DeprecatedException('This should not be called at all!');

		$data = array(
			'name' => $addon->composerName,
			'tags' => $addon->tags,
			'description' => $addon->shortDescription,
			'autoload' => array(
				'classmap' => array('')
			),
		);

		foreach (array('require', 'suggest', 'provide', 'replace', 'conflict') as $section) {
			if ($version->$section) {
				$data[$section] = $version->$section;
			}
		}

		return $data;
	}
}
