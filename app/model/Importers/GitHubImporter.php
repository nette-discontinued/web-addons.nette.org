<?php

namespace NetteAddons\Model\Importers;

use stdClass;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use NetteAddons\Model\Addon;
use NetteAddons\Model\AddonVersion;
use NetteAddons\Model\IAddonImporter;
use NetteAddons\Model\Utils;


class GitHubImporter extends \Nette\Object implements IAddonImporter
{
	/** @var GitHub\Repository */
	private $repository;

	/** @var Utils\Validators */
	private $validators;


	public function __construct(GitHub\Repository $repo, Utils\Validators $validators)
	{
		$this->repository = $repo;
		$this->validators = $validators;
	}


	/**
	 * @return string
	 */
	public static function getName()
	{
		return 'GitHub';
	}


	/**
	 * @param string
	 * @return bool
	 */
	public static function isSupported($url)
	{
		return Strings::match($url, GitHub\Repository::URL_PATTERN_PUBLIC) || Strings::match($url, GitHub\Repository::URL_PATTERN_PRIVATE);
	}


	/**
	 * @param string
	 * @return bool
	 */
	public static function isValid($url)
	{
		return is_array(GitHub\Repository::getVendorAndName($url));
	}


	/**
	 * @param string
	 * @return string
	 */
	public static function normalizeUrl($url)
	{
		$data = Github\Repository::getVendorAndName($url);
		if (is_null($data)) {
			return NULL;
		}
		return 'https://github.com/' . $data[0] . '/' . $data[1];
	}


	/**
	 * Imports addon from GitHub repository.
	 *
	 * @return \NetteAddons\Model\Addon
	 * @throws \NetteAddons\IOException
	 */
	public function import()
	{
		$info = $this->repository->getMetadata();
		if (!isset($info->default_branch, $info->name, $info->description)) {
			throw new \NetteAddons\IOException('GitHub returned invalid response.');
		}

		$readme = $this->repository->getReadme($info->default_branch);
		$composer = $this->getComposerJson($info->default_branch);

		$addon = new Addon();

		// name
		$addon->name = $info->name;

		// composerName
		if ($composer && $this->validators->isComposerFullNameValid($composer->name)) {
			$addon->composerFullName = $composer->name;
		}

		// shortDescription
		if ($composer) {
			$addon->shortDescription = Strings::truncate($composer->description, 250);
		} elseif (!empty($info->description)) {
			$addon->shortDescription = Strings::truncate($info->description, 250);
		}

		// description
		if ($readme) {
			$addon->description = $readme->content;
			$ext = strtolower(pathinfo($readme->path, PATHINFO_EXTENSION));
			$addon->descriptionFormat = in_array($ext, array('md', 'markdown')) ? 'markdown' : 'texy';
		}

		// default license
		if ($composer && isset($composer->license)) {
			$addon->defaultLicense = implode(',', (array) $composer->license);
		}

		// repository
		$addon->repository = $this->repository->getUrl();
		$addon->repositoryHosting = 'github';

		// tags
		if ($composer && isset($composer->keywords)) {
			$addon->tags = $composer->keywords;
		}

		return $addon;
	}


	/**
	 * Imports versions from GitHub repository.
	 *
	 * @param \NetteAddons\Model\Addon
	 * @return \NetteAddons\Model\AddonVersion[]
	 * @throws \NetteAddons\IOException
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
				$version->version = $composer->version; // warning: multiple tags may have the same composer version
			} else {
				$version->version = $v;
			}

			// license
			if ($composer && isset($composer->license)) {
				$version->license = implode(', ', (array) $composer->license);
			} else {
				$version->license = $addon->defaultLicense;
			}

			// package links
			if ($composer) {
				foreach (AddonVersion::getLinkTypes() as $key => $type) {
					if (!empty($composer->$type)) {
						$version->$key = get_object_vars($composer->$type);
					}
				}
			}

			// time
			if ($composer && isset($composer->time)) {
				$version->updatedAt = new \DateTime($composer->time);
			} else {
				$version->updatedAt = new \DateTime(
					$this->repository->getCommit($hash)->commit->author->date
				);
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

			$versions[$version->version] = $version; // ensures that versions are unique
		}

		return array_values($versions);
	}


	/**
	 * Returns list of version in repository.
	 *
	 * @return array (version => hash)
	 */
	private function getVersions()
	{
		$versions = array();
		$tags = $this->repository->getTags();
		$branches = $this->repository->getBranches();
		$util = new Utils\VersionParser; // TODO: use dependency injection

		foreach ($tags as $tag => $hash) {
			$version = $util->parseTag($tag);
			if (!$version) continue;
			$versions[$version] = $tag;
		}

		foreach ($branches as $branch => $hash) {
			$version = $util->parseBranch($branch);
			$versions[$version] = $branch;
		}

		return $versions;
	}


	/**
	 * Returns composer.json or NULL if composer.json does not exist or is invalid.
	 *
	 * @param string commit hash, brach or tag name
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
		} catch (\NetteAddons\Utils\HttpException $e) {
			if ($e->getCode() === 404) {
				return NULL;
			}

			throw $e;
		} catch (\Nette\Utils\JsonException $e) {
			return NULL;
		}
	}
}
