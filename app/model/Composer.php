<?php

namespace NetteAddons\Model;

/**
 * Generating JSON for Composer API
 *
 * @author Jan Marek
 */
class Composer extends \Nette\Object
{

	private $addons;



	public function __construct(Addons $addons)
	{
		$this->addons = $addons;
	}



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
					'description' => $addon->shortDescription,
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
		$composer = $version->composerJson ?: $this->createDefaultComposerJson($addon, $version);
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
