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
	 * @var \Nette\Database\Connection
	 */
	private $connection;



	/**
	 * @param \NetteAddons\Model\Addons $addons
	 * @param \NetteAddons\Model\Tags $tags
	 * @param \NetteAddons\Model\AddonVersions $versions
	 * @param \NetteAddons\Model\VersionDependencies $dependencies
	 * @param \Nette\Database\Connection $connection
	 */
	public function __construct(
		Addons $addons,
		Tags $tags,
		AddonVersions $versions,
		VersionDependencies $dependencies,
		Nette\Database\Connection $connection)
	{
		$this->addons = $addons;
		$this->tags = $tags;
		$this->versions = $versions;
		$this->dependencies = $dependencies;
		$this->connection = $connection;
	}



	/**
	 * @param \NetteAddons\Model\Addon $addon
	 * @throws \NetteAddons\InvalidArgumentException
	 * @throws \NetteAddons\InvalidStateException
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function update(Addon $addon)
	{
		if (!$addon->userId || !$addon->composerName) {
			throw new \NetteAddons\InvalidArgumentException;
		}

		$this->connection->beginTransaction();

		try {
			$addonRow = $this->addons->createOrUpdate(array(
				'composerName' => $addon->composerName,
				'name' => $addon->name,
				'repository' => $addon->repository,
				'description' => $addon->description ? : "",
				'shortDescription' => $addon->shortDescription ? : "",
				'demo' => $addon->demo ? : NULL,
				'updatedAt' => new \Datetime('now'),
				'userId' => $addon->userId
			));

			foreach ($addon->tags as $tag) {
				$this->tags->addAddonTag($addonRow, $tag);
			}


			foreach ($addon->versions as $version) {
				try {
					$versionRow = $this->versions->setAddonVersion($addonRow, $version);
					$this->dependencies->setVersionDependencies($versionRow, $version);

				} catch (\NetteAddons\InvalidArgumentException $e) {
					throw new \NetteAddons\InvalidStateException("Cannot create version {$version->version}.", NULL, $e);
				}
			}

			$this->connection->commit();

		} catch (\Exception $e) {
			$this->connection->rollBack();
			throw $e;
		}

		return $addonRow;
	}

}
