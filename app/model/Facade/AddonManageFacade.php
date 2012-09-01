<?php

namespace NetteAddons\Model\Facade;

use NetteAddons\Model;
use Nette;
use Nette\Utils\Strings;




/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @author Jan Tvrdík
 */
class AddonManageFacade extends Nette\Object
{
	/** @var Model\Addons */
	private $addons;

	/** @var string */
	private $uploadDir;

	/** @var string */
	private $uploadUrl;



	/**
	 * @param Model\Addons
	 * @param string
	 * @param string
	 */
	public function __construct(Model\Addons $addons, $uploadDir, $uploadUrl)
	{
		$this->addons = $addons;
		$this->uploadDir = $uploadDir;
		$this->uploadUrl = $uploadUrl;
	}



	/**
	 * Imports addon using addon importer.
	 *
	 * @param  Model\IAddonImporter
	 * @param  Nette\Security\IIdentity
	 * @return Model\Addon
	 * @throws \NetteAddons\RuntimeException
	 */
	public function import(Model\IAddonImporter $importer, Nette\Security\IIdentity $owner)
	{
		$addon = $importer->import();
		$addon->userId = $owner->getId();

		return $addon;
	}



	public function importVersions(Model\Addon $addon, Model\IAddonImporter $importer, Nette\Security\Identity $owner)
	{
		$addon->versions = $importer->importVersions($addon);

		// add information about author if missing
		foreach ($addon->versions as $version) {
			if (empty($version->composerJson->authors)) {
				$version->composerJson->authors = array(
					(object) array(
						'name' => $owner->name,
						'email' => $owner->email,
					)
				);
			}
		}

		return $addon->versions;
	}



	/**
	 * Fills addon with values (usually from form). Those value must be already validated.
	 * Returned addon is fully valid (except for versions).
	 *
	 * @param  Model\Addon
	 * @param  array
	 * @param  Nette\Security\Identity
	 * @throws \NetteAddons\InvalidArgumentException
	 * @return Model\Addon
	 */
	public function fillAddonWithValues(Model\Addon $addon, array $values, Nette\Security\Identity $owner)
	{
		$always = array('name', 'shortDescription', 'description', 'demo');
		$ifEmpty = array('composerName' => TRUE, 'defaultLicense' => TRUE, 'repository' => FALSE);

		$addon->userId = $owner->getId();

		foreach ($always as $field) {
			if (!array_key_exists($field, $values)) {
				throw new \NetteAddons\InvalidArgumentException("Values does not contain field '$field'.");
			}
			$addon->$field = $values[$field];
		}

		foreach ($ifEmpty as $field => $required) {
			if (empty($addon->$field)) {
				if (empty($values[$field]) && $required) {
					throw new \NetteAddons\InvalidArgumentException("Values does not contain field '$field'.");
				}
				$addon->$field = $values[$field];
			}
		}

		return $addon;
	}



	/**
	 * Creates new addon version from values and adds it to addon.
	 *
	 * @param Model\Addon
	 * @param array
	 * @return Model\AddonVersion
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function addVersionFromValues(Model\Addon $addon, $values)
	{
		if (!$values->license) {
			throw new \NetteAddons\InvalidArgumentException("License is mandatory.");
		}

		if (!$values->version) {
			throw new \NetteAddons\InvalidArgumentException("Version is mandatory.");
		}

		$version = new Model\AddonVersion();
		$version->addon = $addon;
		$version->version = $values->version;
		$version->license = $values->license;

		$fileName = $this->getFileName($version);
		$fileDest = $this->uploadDir . '/' . $fileName;
		$fileUrl = $this->uploadUrl . '/' . $fileName;

		/** @var $file \Nette\Http\FileUpload */
		$file = $values->archive;
		$file->move($fileDest);

		$version->distType = 'zip';
		$version->distUrl = $fileUrl;

		$addon->versions[] = $version;

		return $version;
	}



	/**
	 * Returns filename for addon version.
	 *
	 * @param  Model\AddonVersion
	 * @return string
	 */
	private function getFileName(Model\AddonVersion $version)
	{
		$name = Strings::webalize($version->addon->composerName)
		      . '-' . $version->version . '.zip';

		return $name;
	}
}
