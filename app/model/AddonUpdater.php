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
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function update(Addon $addon)
	{
		$package = array(
			'composer_name' => $addon->composerName
		);

		if (!$addon->userId) {
			throw new \NetteAddons\InvalidArgumentException;
		}

		if (!$addonRow = $this->addons->findOneBy($package)) {
			$addonRow = $this->addons->createRow($package + array(
				'name' => $addon->name,
				'repository' => $addon->repository,
				'description' => $addon->description ?: "",
				'short_description' => $addon->shortDescription ? : "",
				'demo' => $addon->demo ?: NULL,
				'updated_at' => new \Datetime('now'),
				'user_id' => $addon->userId
			));
		}

		foreach ($addon->tags as $tag) {
			$this->tags->addAddonTag($addonRow, $tag);
		}

		foreach ($addon->versions as $version) {
			$versionRow = $this->versions->setAddonVersion($addonRow, $version);
			$this->dependencies->setVersionDependencies($versionRow, $version);
		}

		return $addonRow;
	}

}
