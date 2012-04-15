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
	 * @var string
	 */
	private $uploadDir;



	/**
	 * @param \NetteAddons\Model\AddonUpdater $updater
	 * @param \NetteAddons\Model\Addons $addons
	 * @param string $uploadDir
	 */
	public function __construct(Model\AddonUpdater $updater, Model\Addons $addons, $uploadDir)
	{
		$this->updater = $updater;
		$this->addons = $addons;
		$this->uploadDir = $uploadDir;
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
	 * @param \NetteAddons\Model\RepositoryImporter $importer
	 * @param \Nette\Security\Identity|\Nette\Database\Table\ActiveRow|null $owner
	 *
	 * @throws \NetteAddons\InvalidArgumentException
	 * @throws \UnexpectedValueException
	 * @return \NetteAddons\Model\Addon
	 */
	public function importRepositoryVersions(Model\RepositoryImporter $importer, $owner)
	{
		/** @var \NetteAddons\Model\Addon $addon */
		if (NULL === ($addon = $importer->import())) {
			throw new \UnexpectedValueException("Invalid repository.");
		}

		// validate owner
		if ($owner instanceof Nette\Security\Identity) {
			$addon->userId = $owner->id;

		} elseif ($owner instanceof Nette\Database\Table\ActiveRow) {
			$addon->userId = $owner->id;

		} else {
			throw new \NetteAddons\InvalidArgumentException("Invalid owner was provided");
		}

		// normalize repository
		if (!isset($addon->repository)) {
			$addon->repository = Model\GitHub\Repository::normalizeUrl($importer->getUrl());
		}

		$this->updater->update($addon);
		return $addon;
	}



	/**
	 * @param \NetteAddons\Model\Addon $addon
	 * @param $values
	 *
	 * @throws \NetteAddons\InvalidArgumentException
	 * @return \NetteAddons\Model\AddonVersion
	 */
	public function submitAddonVersion(Model\Addon $addon, $values)
	{
		if (!$values->license) {
			throw new \NetteAddons\InvalidArgumentException("License is mandatory.");
		}

		if (!$values->license) {
			throw new \NetteAddons\InvalidArgumentException("Version is mandatory.");
		}

		$version = new Model\AddonVersion();
		$version->version = $values->version;
		$version->license = $values->license;

		/** @var $file \Nette\Http\FileUpload */
		$file = $values->archive;
		$filename = $version->getFilename($addon);
		$file->move($this->uploadDir . '/' . $filename);
		$version->filename = $filename;

		$addon->versions[] = $version;
		$this->updater->update($addon);
		return $version;
	}

}
