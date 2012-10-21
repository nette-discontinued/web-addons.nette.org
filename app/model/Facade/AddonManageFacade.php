<?php

namespace NetteAddons\Model\Facade;

use stdClass,
	Nette,
	Nette\Utils\Strings,
	Nette\Http\Url,
	Nette\Http\Session,
	Nette\Http\SessionSection,
	NetteAddons\Model,
	NetteAddons\Model\Utils\VersionParser;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @author Jan Tvrdík
 * @author Patrik Votoček
 */
class AddonManageFacade extends Nette\Object
{
	const SESSION_SECTION = 'addons';

	/** @var string */
	private $uploadDir;

	/** @var string */
	private $uploadUrl;

	/** @var \Nette\Http\SessionSection */
	private $session;



	/**
	 * @param \Nette\Http\Session
	 * @param string
	 * @param string
	 */
	public function __construct(Session $session, $uploadDir, $uploadUrl)
	{
		$this->session = $session->getSection(static::SESSION_SECTION);
		$this->uploadDir = $uploadDir;
		$this->uploadUrl = $uploadUrl;
	}



	/**
	 * Imports addon using addon importer.
	 *
	 * @param  Model\IAddonImporter
	 * @param  Nette\Security\IIdentity
	 * @return Model\Addon
	 * @throws \NetteAddons\Utils\HttpException
	 * @throws \NetteAddons\IOException
	 */
	public function import(Model\IAddonImporter $importer, Nette\Security\IIdentity $owner)
	{
		$addon = $importer->import();
		$addon->userId = $owner->getId();

		return $addon;
	}



	/**
	 * Imports versions using addon importer.
	 *
	 * @param  Model\Addon
	 * @param  Model\IAddonImporter
	 * @param  Nette\Security\Identity
	 * @return Model\AddonVersion[]
	 * @throws \NetteAddons\IOException
	 */
	public function importVersions(Model\Addon $addon, Model\IAddonImporter $importer, Nette\Security\Identity $owner)
	{
		return $addon->versions = $this->getImportedVersions($addon, $importer, $owner);
	}



	/**
	 * @throws \NetteAddons\IOException
	 */
	public function updateVersions(Model\Addon $addon, Model\IAddonImporter $importer, Nette\Security\Identity $owner)
	{
		$current = $addon->versions;

		$new = $this->getImportedVersions($addon, $importer, $owner);
		$result = $this->mergeVersions($current, $new);
		$addon->versions = $result['merged'];
		return $result;
	}



	/**
	 * Fills addon with values (usually from form). Those value must be already validated.
	 *
	 * @param  Model\Addon
	 * @param  array
	 * @param  Nette\Security\IIdentity|NULL
	 * @return Model\Addon
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function fillAddonWithValues(Model\Addon $addon, array $values, Nette\Security\IIdentity $owner = NULL)
	{
		$overWritable = array(
			'name' => TRUE,
			'shortDescription' => TRUE,
			'description' => TRUE,
			'descriptionFormat' => TRUE,
			'demo' => TRUE,
			'defaultLicense' => FALSE,
			'repository' => FALSE,
			'repositoryHosting' => FALSE,
			'tags' => FALSE,
		);
		$ifEmpty = array(
			'composerFullName' => TRUE,
		);

		if (isset($values['defaultLicense']) && is_array($values['defaultLicense'])) {
			$values['defaultLicense'] = implode(', ', $values['defaultLicense']);
		}

		if (isset($values['tags']) && is_array($values['tags'])) {
			$values['tags'] = array_map('intval', $values['tags']);
		}

		if ($owner) {
			$addon->userId = $owner->getId(); // TODO: this is duplicity to self::import()
		}

		foreach ($overWritable as $field => $required) {
			if (!array_key_exists($field, $values)) {
				if ($required) {
					throw new \NetteAddons\InvalidArgumentException("Values does not contain field '$field'.");
				}
			} else {
				$addon->$field = $values[$field];
			}
		}

		foreach ($ifEmpty as $field => $required) {
			if (empty($addon->$field)) {
				if (empty($values[$field])) {
					if ($required) {
						throw new \NetteAddons\InvalidArgumentException("Values does not contain field '$field'.");
					}
				} else {
					$addon->$field = $values[$field];
				}
			}
		}

		return $addon;
	}



	/**
	 * Creates new addon version from values and adds it to addon.
	 *
	 * @param  Model\Addon
	 * @param  array
	 * @param  Nette\Security\Identity
	 * @param  VersionParser
	 * @return Model\AddonVersion
	 * @throws \NetteAddons\InvalidArgumentException
	 * @throws \NetteAddons\IOException
	 */
	public function addVersionFromValues(Model\Addon $addon, $values, Nette\Security\Identity $owner, VersionParser $versionParser)
	{
		if (!$values->license) {
			throw new \NetteAddons\InvalidArgumentException("License is mandatory.");
		}

		if (!$values->version) {
			throw new \NetteAddons\InvalidArgumentException("Version is mandatory.");
		}

		$version = new Model\AddonVersion();
		$version->addon = $addon;
		$version->version = $versionParser->parseTag($values->version);
		$version->license = is_array($values->license) ? implode(', ', $values->license) : $values->license;

		if ($values->archiveLink) {
			$version->distType = 'zip';
			$version->distUrl = $values->archiveLink;

		} elseif ($values->archive) {
			$fileName = $this->getFileName($version);
			$fileDest = $this->uploadDir . '/' . $fileName;
			$fileUrl = $this->uploadUrl . '/' . $fileName;

			try {
				$file = $values->archive;
				$file->move($fileDest);
			} catch (\Nette\InvalidStateException $e) {
				throw new \NetteAddons\IOException($e->getMessage(), NULL, $e);
			}

			$version->distType = 'zip';
			$version->distUrl = $fileUrl;

		} else {
			throw new \NetteAddons\InvalidArgumentException();
		}

		$version->composerJson = Model\Utils\Composer::createComposerJson($version);
		$version->composerJson->authors = array(
			(object) array(
				'name' => $owner->realname,
				'email' => $owner->email, // Note: Some users may not like disclosing their e-mail.
			)
		);

		$addon->versions[] = $version;

		return $version;
	}



	/**
	 * Returns versions imported from addon importer.
	 *
	 * @param  Model\Addon
	 * @param  Model\IAddonImporter
	 * @param  Nette\Security\Identity
	 * @return Model\AddonVersion[]
	 * @throws \NetteAddons\IOException
	 */
	private function getImportedVersions(Model\Addon $addon, Model\IAddonImporter $importer, Nette\Security\Identity $owner)
	{
		$versions = $importer->importVersions($addon);

		// add information about author if missing
		$author = new stdClass();
		$author->name = $owner->realname;
		if (!empty($owner->email)) $author->email = $owner->email;
		if (!empty($owner->url)) $author->homepage = $owner->url;

		foreach ($versions as $version) {
			if (empty($version->composerJson->authors)) {
				$version->composerJson->authors = array($author);
			}
		}

		return $versions;
	}



	/**
	 * Returns filename for addon version.
	 *
	 * @param  Model\AddonVersion
	 * @return string
	 */
	private function getFileName(Model\AddonVersion $version)
	{
		$name = Strings::webalize($version->addon->composerFullName)
			. '-' . $version->version . '.zip';

		return $name;
	}



	/**
	 * @param  Model\AddonVersion[]
	 * @param  Model\AddonVersion[]
	 * @return array
	 */
	private function mergeVersions($a, $b)
	{
		$merged = array();
		$new = array();
		$conflicted = array();

		foreach ($a as $version) {
			$merged[$version->version] = $version;
		}

		foreach ($b as $version) {
			if (!isset($merged[$version->version])) {
				$merged[$version->version] = $version;
				$new[$version->version] = $version;

			} else {
				$diff = array_diff_assoc_recursive(
					get_object_vars($version),
					get_object_vars($merged[$version->version])
				);
				unset($diff['id']); // ignore ID diff
				if ($diff) {
					$conflicted[$version->version] = array(
						'a' => $merged[$version->version],
						'b' => $version,
						'diff' => $diff,
					);
				}
			}
		}

		return array(
			'ok' => (count($conflicted) === 0),
			'merged' => array_values($merged),
			'new' => $new,
			'conflicted' => $conflicted,
		);
	}



	/**
	 * @param string
	 * @param \NetteAddons\Model\Addon
	 */
	public function storeAddon($token, Model\Addon $addon)
	{
		$this->session[$token] = $addon;
	}



	/**
	 * @param string
	 * @return \NetteAddons\Model\Addon|NULL
	 */
	public function restoreAddon($token)
	{
		if (isset($this->session[$token])) {
			return $this->session[$token];
		}
	}



	/**
	 * @param string
	 */
	public function destroyAddon($token)
	{
		if (isset($this->session[$token])) {
			unset($this->session[$token]);
		}
	}


	/**
	 * @param \Nette\Http\Session
	 * @param \Nette\Http\Url
	 * @param string
	 * @param string
	 * @return AddonManageFacade
	 */
	public static function create(Session $session, Url $currentUrl, $uploadDir, $uploadUri)
	{
		$url = $currentUrl->getHostUrl() . rtrim($currentUrl->getBasePath(), '/') . $uploadUri;
		return new static($session, $uploadDir, $url);
	}

}