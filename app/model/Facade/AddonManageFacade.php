<?php

namespace NetteAddons\Model\Facade;

use Nette;
use NetteAddons\Model;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AddonManageFacade extends Nette\Object
{
	/**
	 * @var \NetteAddons\Model\AddonUpdater
	 */
	private $updater;

	/**
	 * @var \NetteAddons\Model\Addons
	 */
	private $addons;



	/**
	 * @param \NetteAddons\Model\AddonUpdater $updater
	 * @param \NetteAddons\Model\Addons $addons
	 */
	public function __construct(Model\AddonUpdater $updater, Model\Addons $addons)
	{
		$this->updater = $updater;
		$this->addons = $addons;
	}



	/**
	 * @param \NetteAddons\Model\Addon $addon
	 * @param $values
	 * @param \Nette\Security\Identity $owner
	 *
	 * @return \Nette\Security\Identity
	 */
	public function buildAddonFromValues(Model\Addon $addon, $values, Nette\Security\Identity $owner)
	{
		$addon->name = $values->name;
		$addon->shortDescription = $values->shortDescription;
		$addon->description = $values->description;
		$addon->demo = $values->demo;
		$addon->userId = $owner->getId();

		if ($addon->composerName === NULL) {
			$addon->buildComposerName($owner);
		}

		if ($this->addons->findOneBy(array('composer_name' => $addon->composerName)) !== FALSE) {
			$message = 'Addon with same composer package already exists. ';
			if ($addon->repository) {
				throw new \NetteAddons\DuplicateEntryException($message . 'Please specify another package to import.');

			} else {
				throw new \NetteAddons\DuplicateEntryException($message . 'Please specify another addon name.');
			}
		}

		$this->updater->update($addon);
		return $addon;
	}



	/**
	 * @param \NetteAddons\Model\IAddonImporter $importer
	 * @param $values
	 * @param \Nette\Security\Identity $owner
	 * @throws \UnexpectedValueException
	 * @return \NetteAddons\Model\Addon
	 */
	public function importRepositoryVersions(Model\IAddonImporter $importer, $values, Nette\Security\Identity $owner)
	{
		/** @var \NetteAddons\Model\Addon $addon */
		if (NULL === ($addon = $importer->import())) {
			throw new \UnexpectedValueException("Invalid repository.");
		}

		if (!isset($addon->repository)) {
			$addon->repository = Model\GitHub\Repository::normalizeUrl($values->url);
		}

		$addon->userId = $owner->getId();
		return $addon;
	}

}
