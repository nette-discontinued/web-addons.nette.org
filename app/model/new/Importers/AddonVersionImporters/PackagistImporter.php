<?php

namespace NetteAddons\Model\Importers\AddonVersionImporters;

use Nette\Utils\Strings;
use NetteAddons\Model\AddonDependencyEntity;
use NetteAddons\Model\AddonEntity;
use NetteAddons\Model\AddonVersionEntity;
use NetteAddons\Model\Importers\GitHub\RepositoryFactory;
use NetteAddons\Model\Importers\IAddonVersionsImporter;
use NetteAddons\Model\UrlsHelper;
use Packagist\Api\Client;

class PackagistImporter extends \Nette\Object implements IAddonVersionsImporter
{
	const URL_REGEXP = '(packagist\.org/packages/(?P<vendor>[a-z0-9]+(?:-[a-z0-9]+)*)/(?P<name>[a-z0-9]+(?:-[a-z0-9]+)*))';

	/** @var \Packagist\Api\Client */
	private $apiClient;

	/** @var \NetteAddons\Model\Importers\GitHub\RepositoryFactory */
	private $githubClientFactory;

	public function __construct(RepositoryFactory $githubClientFactory)
	{
		$this->apiClient = new Client;
		$this->githubClientFactory = $githubClientFactory;
	}

	/**
	 * @param string
	 * @return \NetteAddons\Model\AddonEntity
	 */
	public function getAddon($url)
	{
		if (!$this->isSupported($url)) {
			throw new \NetteAddons\InvalidArgumentException('Url "' . $url . '" not supported.');
		}

		$match = Strings::match($url, self::URL_REGEXP);
		$composerFullName = $match['vendor'] . '/' . $match['name'];

		try {
			$data = $this->apiClient->get($composerFullName);

			$addon = new AddonEntity($data->getName());
			$addon->setPerex($data->getDescription());
			$addon->setPackagist(UrlsHelper::normalizePackagistPackageUrl($url));

			foreach ($data->getVersions() as $versionData) {
				/** @var \Packagist\Api\Result\Package\Version $versionData */
				$version = new AddonVersionEntity($addon->getComposerFullName(), $versionData->getVersion());

				if ($versionData->getSource() !== NULL && empty($addon->getGithub())) {
					/** @var \Packagist\Api\Result\Package\Source $source */
					$source = $versionData->getSource();
					if (UrlsHelper::isGithubRepositoryUrl($source->getUrl())) {
						$normalizedGithubUrl = UrlsHelper::normalizeGithubRepositoryUrl($source->getUrl());
						$addon->setGithub($normalizedGithubUrl);
						$githubClient = $this->githubClientFactory->create($normalizedGithubUrl);
						$addon->setStars($githubClient->getMetadata()->stargazers_count);
					}
				}

				if ($versionData->getLicense() !== NULL) {
					foreach ($versionData->getLicense() as $license) {
						$version->addLicense($license);
					}
				}

				if ($versionData->getRequire() !== NULL) {
					foreach ($versionData->getRequire() as $dependencyName => $dependencyVersion) {
						$dependency = new AddonDependencyEntity(
							$addon->getComposerFullName(),
							$version->getVersion(),
							AddonDependencyEntity::TYPE_REQUIRE,
							$dependencyName,
							$dependencyVersion
						);

						$version->addDependency($dependency);
					}
				}

				if ($versionData->getRequireDev() !== NULL) {
					foreach ($versionData->getRequireDev() as $dependencyName => $dependencyVersion) {
						$dependency = new AddonDependencyEntity(
							$addon->getComposerFullName(),
							$version->getVersion(),
							AddonDependencyEntity::TYPE_REQUIRE_DEV,
							$dependencyName,
							$dependencyVersion
						);

						$version->addDependency($dependency);
					}
				}

				if ($versionData->getConflict() !== NULL) {
					foreach ($versionData->getConflict() as $dependencyName => $dependencyVersion) {
						$dependency = new AddonDependencyEntity(
							$addon->getComposerFullName(),
							$version->getVersion(),
							AddonDependencyEntity::TYPE_CONFLICT,
							$dependencyName,
							$dependencyVersion
						);

						$version->addDependency($dependency);
					}
				}

				if ($versionData->getReplace() !== NULL) {
					foreach ($versionData->getReplace() as $dependencyName => $dependencyVersion) {
						$dependency = new AddonDependencyEntity(
							$addon->getComposerFullName(),
							$version->getVersion(),
							AddonDependencyEntity::TYPE_REPLACE,
							$dependencyName,
							$dependencyVersion
						);

						$version->addDependency($dependency);
					}
				}

				if ($versionData->getProvide() !== NULL) {
					foreach ($versionData->getProvide() as $dependencyName => $dependencyVersion) {
						$dependency = new AddonDependencyEntity(
							$addon->getComposerFullName(),
							$version->getVersion(),
							AddonDependencyEntity::TYPE_PROVIDE,
							$dependencyName,
							$dependencyVersion
						);

						$version->addDependency($dependency);
					}
				}

				// @todo suggest

				$addon->addVersion($version);
			}

			return $addon;
		} catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
			if ($e->getResponse()->getStatusCode() === 404) {
				throw new \NetteAddons\Model\Importers\AddonVersionImporters\AddonNotFoundException(
					'Addon "' . $composerFullName . '" not found', $composerFullName, $e
				);
			}
			throw $e;
		}
	}

	/**
	 * @param string
	 * @return boolean
	 */
	public function isSupported($url)
	{
		return UrlsHelper::isPackagistPackageUrl($url);
	}
}
