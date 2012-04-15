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
	 * @var \NetteAddons\Model\Addons
	 */
	private $addons;

	/**
	 * @var string
	 */
	private $uploadDir;



	/**
	 * @param \NetteAddons\Model\Addons $addons
	 * @param string $uploadDir
	 */
	public function __construct(Model\Addons $addons, $uploadDir)
	{
		$this->addons = $addons;
		$this->uploadDir = $uploadDir;
	}



	/**
	 * @param \NetteAddons\Model\Addon $addon
	 * @param $values
	 * @param \Nette\Security\Identity $owner
	 *
	 * @throws \NetteAddons\DuplicateEntryException
	 * @return \Nette\Security\Identity
	 */
	public function buildAddonFromValues(Model\Addon $addon, $values, Nette\Security\Identity $owner)
	{
		$addon->name = $values->name;
		$addon->shortDescription = $values->shortDescription;
		$addon->description = $values->description;
		$addon->demo = $values->demo;

		if ($addon->composerName === NULL) {
			$addon->buildComposerName($owner);
		}

		if ($this->addons->findOneBy(array('composerName' => $addon->composerName)) !== FALSE) {
			$message = 'Addon with same composer package already exists. ';
			if ($addon->repository) {
				throw new \NetteAddons\DuplicateEntryException($message . 'Please specify another package to import.');

			} else {
				throw new \NetteAddons\DuplicateEntryException($message . 'Please specify another addon name.');
			}
		}

		$addon->userId = $owner->getId();
		return $addon;
	}



	/**
	 * @param \NetteAddons\Model\Importers\GitHubImporter
	 * @param \Nette\Security\Identity|\Nette\Database\Table\ActiveRow|null $owner
	 *
	 * @throws \NetteAddons\InvalidArgumentException
	 * @throws \UnexpectedValueException
	 * @return \NetteAddons\Model\Addon
	 */
	public function importRepository(Model\Importers\GitHubImporter $importer, $owner)
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
			$addon->repository = \NetteAddons\Model\Importers\GitHub\Helpers::normalizeRepositoryUrl($importer->getUrl());
		}

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

		if (!$values->version) {
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
		return $version;
	}

}
