<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AddonUpdater extends Nette\Object
{

	/**
	 * @var Addons
	 */
	private $addons;

	/**
	 * @var Tags
	 */
	private $tags;

	/**
	 * @var AddonVersions
	 */
	private $versions;

	/**
	 * @var VersionDependencies
	 */
	private $dependencies;



	/**
	 * @param \NetteAddons\Model\Addons $addons
	 * @param \NetteAddons\Model\Tags $tags
	 * @param \NetteAddons\Model\AddonVersions $versions
	 * @param \NetteAddons\Model\VersionDependencies $dependencies
	 */
	public function __construct(Addons $addons, Tags $tags, AddonVersions $versions, VersionDependencies $dependencies)
	{
		$this->addons = $addons;
		$this->tags = $tags;
		$this->versions = $versions;
		$this->dependencies = $dependencies;
	}



	/**
	 * @param \NetteAddons\Model\Addon $addon
	 */
	public function update(Addon $addon)
	{
		if (!$addonRow = $this->addons->findOneBy(array('name' => $addon->name))) {
			$addonRow = $this->addons->createRow(array(
				'name' => $addon->name,
				'repository' => $addon->repository,
				'description' => $addon->description,
				// todo: author
			));
		}

		foreach ($addon->tags as $tag) {
			$this->tags->addAddonTag($addonRow, $tag);
		}

		foreach ($addon->versions as $version) {
			$this->versions->setAddonVersion($addonRow, $version);
		}
	}

}
