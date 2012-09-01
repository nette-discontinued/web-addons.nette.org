<?php

namespace NetteAddons\Model\Importers;

use NetteAddons\Model;
use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonVersion;
use NetteAddons\Model\IAddonImporter;
use NetteAddons\Model\Utils;
use Nette;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use stdClass;



/**
 * @author Patrik Votoček
 * @author Jan Tvrdík
 */
class GitHubImporter extends Nette\Object implements IAddonImporter
{
	/** @var GitHub\Repository */
	private $repository;



	/**
	 * @param GitHub\Repository
	 */
	public function __construct(GitHub\Repository $repo)
	{
		$this->repository = $repo;
	}



	/**
	 * @todo remove!
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}



	/**
	 * Imports addon from GitHub repository.
	 *
	 * @return Addon
	 * @throws \NetteAddons\IOException
	 */
	public function import()
	{
		$info = $this->repository->getMetadata();
		if (!isset($info->master_branch, $info->name, $info->description)) {
			throw new \NetteAddons\IOException('GitHub returned invalid response.');
		}

		$readme = $this->repository->getReadme($info->master_branch);
		$composer = $this->getComposerJson($info->master_branch);

		$addon = new Addon();

		// name
		$addon->name = $info->name;

		// composerName
		if ($composer) {
			$addon->composerName = $composer->name;
		}

		// shortDescription
		if ($composer) {
			$addon->shortDescription = Strings::truncate($composer->description, 250);
		} elseif (!empty($info->description)) {
			$addon->shortDescription = Strings::truncate($info->description, 250);
		}

		// description
		if ($readme) {
			$addon->description = $readme;
		}

		// default license
		if ($composer && isset($composer->license)) {
			$addon->defaultLicense = implode(',', (array) $composer->license);
		}

		// repository
		$addon->repository = $this->repository->getUrl();

		// tags
		if ($composer && isset($composer->keywords)) {
			$addon->tags = $composer->keywords;
		}

		return $addon;
	}



	/**
	 * Imports versions from GitHub repository.
	 *
	 * @param  Addon
	 * @return AddonVersion[]
	 */
	public function importVersions(Addon $addon)
	{
		$versions = array();

		foreach ($this->getVersions() as $v => $hash) {
			$composer = $this->getComposerJson($hash);

			$version = new AddonVersion();
			$version->addon = $addon;

			// version
			if ($composer && isset($composer->version)) {
				$version->version = $composer->version;
			} else {
				$version->version = $v;
			}

			// license
			if ($composer && isset($composer->license)) {
				$version->license = implode(',', (array) $composer->license);
			} else {
				$version->license = $addon->defaultLicense;
			}

			// package links
			if ($composer) {
				foreach (AddonVersion::getLinkTypes() as $link) {
					if (!empty($composer->$link)) {
						$version->$link = $composer->$link;
					}
				}
			}

			// dist
			$version->distType = 'zip';
			$version->distUrl = $this->repository->getArchiveLink('zip', $hash);

			// source
			$version->sourceType = 'git';
			$version->sourceUrl = $addon->repository;
			$version->sourceReference = $hash;

			// composer.json
			$version->composerJson = Utils\Composer::createComposerJson($version, $composer);

			$versions[] = $version;
		}

		return $versions;
	}



	/**
	 * Returns list of version in repository.
	 *
	 * @return array (version => hash)
	 */
	private function getVersions()
	{
		$tags = $this->repository->getTags();
		$versions = array();
		foreach ($tags as $tag => $hash) {
			$version = Model\Version::create($tag);
			if ($version && $version->isValid()) {
				$versions[$version->getVersion()] = $hash;
			}
		}
		return $versions;
	}



	/**
	 * Returns composer.json or NULL if composer.json does not exist or is invalid.
	 *
	 * @param  string commit hash, brach or tag name
	 * @return stdClass|NULL
	 * @throws \NetteAddons\IOException
	 */
	private function getComposerJson($hash)
	{
		try {
			$content = $this->repository->getFileContent($hash, Utils\Composer::FILENAME);
			$composer = Json::decode($content);
			if (!Utils\Composer::isValid($composer)) {
				return NULL; // invalid composer.json
			}
			return $composer;

		} catch (\NetteAddons\HttpException $e) {
			if ($e->getCode() === 404) {
				return NULL;
			}
			throw $e;

		} catch (\Nette\Utils\JsonException $e) {
			return NULL;
		}
	}
}
