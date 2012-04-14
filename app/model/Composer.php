<?php

namespace NetteAddons\Model;

/**
 * Generating JSON for Composer API
 *
 * @author Jan Marek
 */
class Composer extends \Nette\Object
{

	/**
	 * Generate packages.json data
	 *
	 * @param Addon[] $addons
	 * @return array
	 */
	public function createPackages(array $addons)
	{
		$packages = array();

		foreach ($addons as $addon) {
			$addonName = $addon->composerName;

			$versions = array();

			foreach ($addon->versions as $version) {
				$versions[$version->version] = $this->createComposerJson($addon, $version);
			}

			$packages[] = array(
				$addonName => array(
					'name' => $addonName,
					'description' => $addon->description,
					'versions' => $versions,
				),
			);
		}

		return array(
			'packages' => $packages,
		);
	}

	/**
	 * Generate composer.json data
	 *
	 * @param Addon $addon
	 * @param AddonVersion $version
	 * @return array
	 */
	public function createComposerJson(Addon $addon, AddonVersion $version)
	{
		// use default version
		if ($version->composerJson) {
			$composer = $version->composerJson;
			$composer['version'] = $version->version;
			return $composer;
		}

		$data = array(
			'name' => $addon->composerName,
			'tags' => $addon->tags,
			'description' => $addon->shortDescription,
			'version' => $version->version,
		);

		foreach (array('require', 'suggest', 'provide', 'replace', 'conflict') as $name => $section) {
			if ($version->$section) {
				$data[$section] = $version->$section;
			}
		}

		return $data;
	}

}
