<?php

namespace NetteAddons\Model;

/**
 * @author Jan Marek
 */
class Composer extends \Nette\Object
{

	/**
	 * @param Addon[] $addons
	 * @return array
	 */
	public function createPackages(array $addons)
	{
		$packages = array();

		foreach ($addons as $addon) {
			$addonName = $this->getFullName($addon);

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

	public function createComposerJson(Addon $addon, AddonVersion $version)
	{
		// use default version
		if ($version->composerJson) {
			$composer = $version->composerJson;
			$composer['version'] = $version->version;
			return $composer;
		}

		$data = array(
			'name' => $this->getFullName($addon),
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

	public function getFullName(Addon $addon)
	{
		return $addon->vendorName . '/' . $addon->name;
	}

}
