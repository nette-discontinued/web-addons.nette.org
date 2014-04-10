<?php

namespace NetteAddons\Model\Facade;

use stdClass;
use Nette\Security\IIdentity;
use Nette\Utils\Strings;
use Nette\Http\Url;
use Nette\Http\Session;
use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonVersion;
use NetteAddons\Model\IAddonImporter;
use NetteAddons\Model\Utils\VersionParser;
use NetteAddons\Model\Utils\Composer;


class AddonManageFacade extends \Nette\Object
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
	 * @param \NetteAddons\Model\IAddonImporter
	 * @param \Nette\Security\IIdentity
	 * @return \NetteAddons\Model\Addon
	 * @throws \NetteAddons\Utils\HttpException
	 * @throws \NetteAddons\IOException
	 */
	public function import(IAddonImporter $importer, IIdentity $owner)
	{
		$addon = $importer->import();
		$addon->userId = $owner->getId();

		return $addon;
	}


	/**
	 * Imports versions using addon importer.
	 *
	 * @param \NetteAddons\Model\Addon
	 * @param \NetteAddons\Model\IAddonImporter
	 * @param \Nette\Security\IIdentity
	 * @return \NetteAddons\Model\AddonVersion[]
	 * @throws \NetteAddons\IOException
	 */
	public function importVersions(Addon $addon, IAddonImporter $importer, IIdentity $owner)
	{
		return $addon->versions = $this->getImportedVersions($addon, $importer, $owner);
	}


	/**
	 * Update versions using addon importer.
	 *
	 * @param \NetteAddons\Model\Addon
	 * @param \NetteAddons\Model\IAddonImporter
	 * @param \Nette\Security\IIdentity
	 * @return \NetteAddons\Model\AddonVersion[]
	 * @throws \NetteAddons\IOException
	 */
	public function updateVersions(Addon $addon, IAddonImporter $importer, IIdentity $owner)
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
	 * @param \NetteAddons\Model\Addon
	 * @param array
	 * @param \Nette\Security\IIdentity|NULL
	 * @return \NetteAddons\Model\Addon
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function fillAddonWithValues(Addon $addon, array $values, IIdentity $owner = NULL)
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
	 * @param \NetteAddons\Model\Addon
	 * @param array
	 * @param \Nette\Security\IIdentity
	 * @param \NetteAddons\Model\Utils\VersionParser
	 * @return \NetteAddons\Model\AddonVersion
	 * @throws \NetteAddons\InvalidArgumentException
	 * @throws \NetteAddons\IOException
	 */
	public function addVersionFromValues(Addon $addon, $values, IIdentity $owner, VersionParser $versionParser)
	{
		if (!$values->license) {
			throw new \NetteAddons\InvalidArgumentException("License is mandatory.");
		}

		if (!$values->version) {
			throw new \NetteAddons\InvalidArgumentException("Version is mandatory.");
		}

		$version = new AddonVersion;
		$version->addon = $addon;
		$version->version = $versionParser->parseTag($values->version);
		$version->license = is_array($values->license) ? implode(', ', $values->license) : $values->license;

		$version->distType = 'zip';
		$version->distUrl = $values->archiveLink;

		$version->composerJson = Composer::createComposerJson($version);
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
	 * @param \NetteAddons\Model\Addon
	 * @param \NetteAddons\Model\IAddonImporter
	 * @param \Nette\Security\IIdentity
	 * @return \NetteAddons\Model\AddonVersion[]
	 * @throws \NetteAddons\IOException
	 */
	private function getImportedVersions(Addon $addon, IAddonImporter $importer, IIdentity $owner)
	{
		$versions = $importer->importVersions($addon);

		// add information about author if missing
		$author = new stdClass();
		$author->name = $owner->realname;

		if (!empty($owner->email)) {
			$author->email = $owner->email;
		}

		if (!empty($owner->url)) {
			$author->homepage = $owner->url;
		}

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
	 * @param \NetteAddons\Model\AddonVersion
	 * @return string
	 */
	private function getFileName(AddonVersion $version)
	{
		$name = Strings::webalize($version->addon->composerFullName)
			. '-' . $version->version . '.zip';

		return $name;
	}



	/**
	 * @param \NetteAddons\Model\AddonVersion[]
	 * @param \NetteAddons\Model\AddonVersion[]
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
	public function storeAddon($token, Addon $addon)
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
